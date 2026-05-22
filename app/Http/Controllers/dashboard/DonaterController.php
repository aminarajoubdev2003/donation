<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonaterResource;
use App\Http\Resources\DonatersResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DonaterController extends Controller
{
    use GeneralTrait;
    public function Get_Donaters(){
       $donations = Donation::whereHas('user', function ($query) {
        $query->where('type', 'فردي');
        })->latest()->get();

        $donations = DonatersResource::collection( $donations );
        return $this->apiResponse( $donations );
    }

    public function searchByname( Request $request){

        $validate = Validator::make($request->all(), [
       "name" => "required|string|min:3|max:30|regex:/^[\p{Arabic}\s]+$/u" ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $donaters = Donation::whereHas('user', function ($query , $request) {
        $query->where('name', $request->name);
        })->get();

        if( $donaters ){
        $donaters = DonatersResource::collection( $donaters );
        return $this->apiResponse($donaters);
        }
        else{
            return $this->apiResponse([]);
        }
    }

    public function getTotal( $uuid ){
       $user = User::with('donations')->where('uuid', $uuid)->firstOrFail();
       return $user->total_donations;
    }

    public function getCount( $uuid ){
        $count = Donation::whereHas('user', function ($query, $uuid) {
        $query->where('uuid', $uuid);
        })->count();

        return $count;
    }

    public function getaverage( $uuid ){
       $total = $this->getTotal($uuid);
       $count = $this->getCount($uuid);
       return $total/$count;
    }

    public function getLast( $uuid ){
        $last = Donation::whereHas('user', function ($query, $uuid) {
        $query->where('uuid', $uuid);
        })->latest()->first();
        
        return $last;
    }
}
