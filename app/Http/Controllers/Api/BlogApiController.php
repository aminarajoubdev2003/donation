<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Blog;
use Illuminate\Http\Request;

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

}
