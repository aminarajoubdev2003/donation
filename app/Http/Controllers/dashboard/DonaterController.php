<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Donater_ShowResource;
use App\Http\Resources\DonatersResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DonaterController extends Controller
{
    use GeneralTrait;

    public function Get_Donaters(){
        try{
       $donations = Donation::whereHas('user', function ($query) {
        $query->where('type', 'فردي');
        })->latest()->get();

        if($donations->isNotEmpty()){
        $donations = DonatersResource::collection( $donations );
        return $this->apiResponse( $donations );
        }else{
            return $this->apiResponse( [] );
        }
        }catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function Get_bussinessman(){
        try{
       $donations = Donation::whereHas('user', function ($query) {
        $query->where('type', 'رجال أعمال');
        })->latest()->get();

        if($donations->isNotEmpty()){
        $donations = DonatersResource::collection( $donations );
        return $this->apiResponse( $donations );
        }else{
            return $this->apiResponse( [] );
        }
        }catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function Get_orgnasation(){
        try{
       $donations = Donation::whereHas('user', function ($query) {
        $query->where('type', 'منظمات');
        })->latest()->get();

        if($donations->isNotEmpty()){
        $donations = DonatersResource::collection( $donations );
        return $this->apiResponse( $donations );
        }else{
            return $this->apiResponse( [] );
        }
        }catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function searchByname( Request $request){
        try{
        $validate = Validator::make($request->all(), [
       "name" => "string|regex:/^[\p{Arabic}\s]+$/u" ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $donaters = Donation::whereHas('user', function ($query) use ($request) {
        $query->where('name', 'LIKE', '%' . $request->name . '%');
        })->get();

        if( $donaters->isNotEmpty() ){
        $donaters = DonatersResource::collection( $donaters );
        return $this->apiResponse($donaters);
        }
        else{
            return $this->apiResponse([]);
        }
        } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
        }
    }

    public function filter(Request $request){
    try{
        $validate = Validator::make($request->all(), [
       "name" => "string|regex:/^[\p{Arabic}\s]+$/u" ]);

       $donate_directly = 0; $pledge_to_donate = 0; $pending=0;

    if( filled( $request->method)){
        if( $request->method == 'تبرع'){
            $donate_directly =1;
            $pledge_to_donate =0;
        }else{
            $pledge_to_donate =1;
            $donate_directly =0;
        }
    }
    if( filled( $request->pending)){
        if( $request->pending == 'مدفوع'){
            $pending = 1;
        }else{
            $pending = 0;
        }
    }
    $donations = Donation::query()
    ->when($request->name, function ($q) use ($request) {
            $q->whereHas('user', function ($q2) use ($request) {
                $q2->where('name', 'LIKE', '%' . $request->name . '%');
            });
        })
    ->when($request->method, function ($q) use ($pledge_to_donate) {
            $q->where('pledge_to_donate', $pledge_to_donate);
        })
        ->when($request->method, function ($q) use ($donate_directly) {
            $q->where('donate_directly', $donate_directly);
        })
        ->when($request->status, function ($q) use ($request) {
            $q->where('status', $request->status);
        })
        ->when($request->pending, function ($q) use ($pending) {
            $q->where('pending', $pending);
        })
        ->when($request->currency_type, function ($q) use ($request) {
            $q->where('currency_type', $request->currency_type);
        })
        ->get();

    return $this->apiResponse(DonatersResource::collection($donations));
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }}


    public function show( $uuid ){
        try{
       $user = User::with('donations')->where('uuid', $uuid)->firstOrFail();

        $donations = Donation::where('user_id',$user->id)->latest()->get();

        if($donations->isNotEmpty()){
        return response()->json([
        'status' => true,
        'data' => [
            'user' => UserResource::make($user),
            'donations' => Donater_ShowResource::collection( $donations ),
            'total_donations' =>$user->total_donations,
            'donations_count' =>$user->donations_count,
            'average_donations' =>$user->donations_count > 0 ? $user->average_donations : 0,

            'last_donation' => !empty($user->last_donation)?
                ['amount' =>$user->last_donation->contribution_amount,
                'currency_type' =>$user->last_donation->currency_type,]: null,
            ]
        ], 200);
        }else{
        return response()->json([
        'status' => true,
        'data' => [
            'user' => UserResource::make($user),
            'donations' => [],
            'total_donations' =>0,
            'donations_count' =>0,
            'average_donations' =>0,
            'last_donation' => 0,
        ], 200]);
        }
        } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function getCurrencyType(){
    try{
        $currency_type = ['SYP', 'USD', 'EUR'];
        return $this->apiResponse($currency_type);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function getStatus(){
    try{
        $status = ['متوافق', 'غير متوافق', 'قيد التدقيق'];
        return $this->apiResponse($status);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function getMethod(){
    try{
        $method = ['تعهد' , 'تبرع'];
        return $this->apiResponse($method);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function getPending(){
    try{
        $pending = ['مدفوع' , 'غير مدفوع'];
        return $this->apiResponse($pending);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }
}
