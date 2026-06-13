<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BlogApiController extends Controller
{
    use GeneralTrait;

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

    public function show( $uuid ){
        try{

        $blog = Blog::where('uuid', $uuid)->firstOrFail();
        return $this->apiResponse(BlogResource::make($blog));

        }catch (\Exception $ex) {
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
       "title" => "string|regex:/^[\p{Arabic}\s]+$/u" ],
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
    ->get();

    return $this->apiResponse(BlogResource::collection($blogs));
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }}

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

    public function get_sector(){
    try{
        /*$categories = Blog::whereNotNull('category')->pluck('category');
        $onTheOtherHand = Project::whereNotNull('on_the_other_hand')->pluck('on_the_other_hand');
        $allData = $sectors->merge($onTheOtherHand)->unique() ->values();*/
        $sectors = ['تعليمي', 'صحي', 'إغاثي', 'إعمار', 'غير ذلك'];
        return $this->apiResponse($sectors);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

}
