<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    use GeneralTrait , UploadTrait;
    public function store(Request $request)
    {
    try {

        $category = ['أخبار المشاريع','حملات جديدة','تقارير التوزيع','قصص نجاح',
        'تنبيهات عاجلة','فعاليات','شركات و منظمات','غير ذلك'];

        $validate = Validator::make($request->all(), [
            "title" =>"required|unique|string|min:10|max:200|regex:/^[\p{Arabic}\s]+$/u",
            "category" => ["required", Rule::in($category)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "images" => "required|array",
            "images.*" => "image|mimes:jpg,jpeg,png",
            "excerpt" =>"required|string|min:3|max:200|regex:/^[\p{Arabic}\s]+$/u",
            "content" =>"required|text|regex:/^[\p{Arabic}\s0-9\p{P}\p{S}]+$/u",
        ],[
            'title.unique' => 'هذا العنوان موجود مسيقا',
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }


        if ($request->hasFile('images')) {
            $images = $this->upload_files($request->file('images'),'Blogs/images');
        }

        /*$blog = Blade::create([
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'category' => $request->category,
            'on_the_other_hand' => $request->on_the_other_hand,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'images' => $images
        ]);

        return $this->apiResponse(BlogResource::make($blog));*/return 'klnm';

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function update(Request $request, $uuid)
    {
    try {

        $category = ['أخبار المشاريع','حملات جديدة','تقارير التوزيع','قصص نجاح',
        'تنبيهات عاجلة','فعاليات','شركات و منظمات','غير ذلك'];

        $validate = Validator::make($request->all(), [
            "title" =>"string|unique|min:3|max:200|regex:/^[\p{Arabic}\s]+$/u",
            "category" => [ Rule::in($category)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "images" => "array",
            "images.*" => "image|mimes:jpg,jpeg,png",
            "excerpt" =>"string|min:3|max:200|regex:/^[\p{Arabic}\s]+$/u",
            "content" =>"text|regex:/^[\p{Arabic}\s0-9\p{P}\p{S}]+$/u",
        ],[
            'title.unique' => 'هذا العنوان موجود مسيقا',
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }
        $blog = Blog::where('uuid', $uuid)->firstOrFail();

        if ( !$request->on_the_other_hand ){
            $on_the_other_hand = null;
        }

         if ($request->hasFile('images')) {
                if ($blog->images) {
                    $this->delete_files($blog->images);
                }
            $images = $this->upload_files($request->file('images'), 'blogs/images');
            }else{
                $images = $blog->images;
            }

        $data = [
            'title' => $request->title,
            'category' => $request->category,
            'on_the_other_hand' => $request->on_the_other_hand,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'images' => $images
        ];
        $blog->update($data);

        return $this->apiResponse(BlogResource::make($blog));

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function filter(Request $request){
    try{
    $blogs = Blog::query()
    ->when($request->category, function ($q) use ($request) {
            $q->where('category', $request->category);
        })
        ->get();

    return $this->apiResponse(BlogResource::collection($blogs));
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }}

    public function searchBytitle( Request $request){
        try{
        $validate = Validator::make($request->all(), [
       "title" => "string|regex:/^[\p{Arabic}\s]+$/u" ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $blogs = Blog::where('title', 'LIKE', '%' . $request->title . '%')->get();

        if( $blogs->isNotEmpty() ){
        $donaters = BlogResource::collection( $blogs );
        return $this->apiResponse($donaters);
        }
        else{
            return $this->apiResponse([]);
        }
        } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
        }
    }


    public function getCategory(){
    try{
        $category = ['أخبار المشاريع','حملات جديدة','تقارير التوزيع','قصص نجاح',
        'تنبيهات عاجلة','فعاليات','شركات و منظمات','غير ذلك'];
        return $this->apiResponse($category);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function index(){
    try{
       $blogs = Blog::latest()->get();
       if( $blogs->isNotEmpty()){
            return $this->apiResponse($blogs);
        }else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function getOldest(){
    try{
       $blogs = Blog::oldest()->get();
        if( $blogs->isNotEmpty()){
            return $this->apiResponse($blogs);
        }else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }
    public function getLatest(){
    try{
       $blogs = Blog::latest()->get();
        if( $blogs->isNotEmpty()){
            return $this->apiResponse($blogs);
        }else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }

    }
}
