<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Traits\GeneralTrait;
use App\Http\Resources\DonaterResource;

class IndividualController extends Controller
{
    use GeneralTrait;
    public function Get_Donaters(){
        $donaters = User::where('type' , 'فردي')->get();
        return $this->apiResponse( DonaterResource::collection($donaters) );
    }
}
