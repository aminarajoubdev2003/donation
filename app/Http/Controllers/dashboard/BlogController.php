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
            "title" =>"required|unique:blogs,title|string|min:10|max:200|regex:/^[\p{Arabic}\s]+$/u",
            "category" => ["required", Rule::in($category)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "images" => "required|array",
            "images.*" => "image|mimes:jpg,jpeg,png",
            "cover_image" => "required|image|mimes:jpg,jpeg,png",
            "excerpt" =>"required|string|min:3|max:200|regex:/^[\p{Arabic}\s]+$/u",
            "content" =>"required|string|regex:/^[\p{Arabic}a-zA-Z\s0-9\p{P}\p{S}]+$/u",
        ],[
            'title.unique' => 'هذا العنوان موجود مسيقا',
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        if ($request->hasFile('cover_image')) {
            $cover_image = $this->upload_file($request->file('cover_image'),'Blogs/images');
        }


        if ($request->hasFile('images')) {
            $images = $this->upload_files($request->file('images'),'Blogs/images');
        }

        $blog = Blog::create([
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'category' => $request->category,
            'on_the_other_hand' => $request->on_the_other_hand,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'cover_image' => $cover_image,
            'images' => $images
        ]);

        return $this->apiResponse(BlogResource::make($blog));

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function update(Request $request, $uuid)
    {
    try {
        $blog = Blog::where('uuid', $uuid)->firstOrFail();
        $category = ['أخبار المشاريع','حملات جديدة','تقارير التوزيع','قصص نجاح',
        'تنبيهات عاجلة','فعاليات','شركات و منظمات','غير ذلك'];

        $validate = Validator::make($request->all(), [
            "title" =>["string","min:3","max:200","regex:/^[\p{Arabic}\s]+$/u",
            Rule::unique('blogs', 'title')->ignore($blog->id)],
            "category" => [ Rule::in($category)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "cover_image" => "image|mimes:jpg,jpeg,png",
            "excerpt" =>"string|min:3|max:200|regex:/^[\p{Arabic}\s]+$/u",
            "content" =>"string|regex:/^[\p{Arabic}a-zA-Z\s0-9\p{P}\p{S}]+$/u",
        ],[
            'title.unique' => 'هذا العنوان موجود مسيقا',
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        if ($request->hasFile('cover_image')) {
            // حذف الصورة القديمة إذا وجدت
                if ($blog->cover_image) {
                    $this->delete_file($blog->cover_image);
                }
            $cover_image = $this->upload_file($request->file('cover_image'), 'Blogs/images');
            }else{
                $cover_image = $blog->cover_image;
            }

        $data = [
            'title' => $request->title,
            'category' => $request->category,
            'on_the_other_hand' => $request->on_the_other_hand,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'cover_image' => $cover_image
        ];
        $blog->update($data);
        return $this->apiResponse(BlogResource::make($blog));

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function filter(Request $request){
    try{
        $validate = Validator::make($request->all(), [
        'category' => ['nullable', 'array'],
        'category.*' => [
        Rule::in(['أخبار المشاريع','حملات جديدة','تقارير التوزيع','قصص نجاح',
        'تنبيهات عاجلة','فعاليات','شركات و منظمات','غير ذلك']),
        ],
        "title" => "string|regex:/^[\p{Arabic}\s]+$/u" ,
       'method' =>  Rule::in(['من الأحدث , من الأقدم'])
       ]);

    $blogs = Blog::query()
    ->when($request->title, function ($q) use ($request) {
            $q->where('title', 'LIKE', '%' . $request->title . '%');
        })
    ->when($request->category, function ($q) use ($request) {
            if (is_array($request->category)) {
                $q->whereIn('category', $request->category);
            } else {
                $q->where('category', $request->category);
            }
        })
    ->when($request->method == 'من الأحدث', function ($q) {
            $q->latest();
        })

    ->when($request->method == 'من الأقدم', function ($q) {
        $q->oldest();
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
            return $this->apiResponse( BlogResource::collection( $blogs ));
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

    public function delete( $uuid ){
    try{
        $blog = Blog::where('uuid', $uuid)->firstOrFail();
        if( $blog->delete() ){
            return $this->index();
        }else{
            return $this->apiResponse(null, false, 'Failed to delete project', 400);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function restore( $uuid ){
    try{
        $blog = Blog::withTrashed()
        ->where('uuid', $uuid)
        ->firstOrFail();

        if($blog->restore()){
            return $this->index();
        }else{
            return $this->apiResponse(null, false, 'Failed to restore project', 400);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }


    public function deleted( ){
    try{
        $blogs = Blog::onlyTrashed()->get();

        if( $blogs ){
        $blogs = BlogResource::collection($blogs);
        return $this->apiResponse( $blogs );
        }
        else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function upload( Request $request , $uuid){
    try {
        $validate = Validator::make($request->all(), [
            "images" => "required|array",
            "images.*" => "image|mimes:jpg,jpeg,png",
        ]);


        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{

        $blog = Blog::where('uuid', $uuid)->firstOrFail();
        $data = [];

        if ($request->hasFile('images')) {
            $oldImages = $blog->images ?? [];
            $newImages = $this->upload_files($request->file('images'),'Blogs/images');
            $data['images'] = array_merge($oldImages, $newImages);
        }


        if (!empty($data)) {
            $blog->update($data);
        }
            return $this->apiResponse( new BlogResource($blog) );
        }

    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }


    public function deleteImageUsingModel($uuid, $index)
    {
    try {
        $blog = Blog::where('uuid', $uuid)->firstOrFail();

        // استخدام الدالة المساعدة من الموديل
        $blog->removeImageByIndex($index);

        return $this->apiResponse( new BlogResource($blog) );

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function show( $uuid ){
        try{

        $blog = Blog::where('uuid', $uuid)->firstOrFail();
        return $this->apiResponse(BlogResource::make($blog));

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }
}
