<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonaterResource;
use App\Models\User;
use App\Http\Traits\GeneralTrait;
use Illuminate\Http\Request;

class DonatersController extends Controller
{
    use GeneralTrait;
    public function Get_Donaters(){
        $donaters = User::where('type' , 'فردي')->get();
       // return $this->apiResponse( DonaterResource::collection($donaters) );
    }
}
