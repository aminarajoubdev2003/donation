<?php

namespace App\Http\Controllers\mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inkind_donationResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Donation;
use App\Models\Governorate;
use App\Models\Inkind_donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Inkind_donationController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function store(Request $request)
    {
    try {

        $type = ['أثاث' ,'أدوات منزلية', 'أجهزة طبية', 'أجهزة إلكترونية', 'ملابس', 'أدوات مدرسية', 'غير ذلك'];
        $status_of_materail = ['جديدة','مستعملة'];

        $validate = Validator::make($request->all(), [
            "governorate_uuid" => "required|string|exists:governorates,uuid",
            "name_of_material" =>"required|string|min:3|max:100|regex:/^[\p{Arabic}\s]+$/u",
            "amount" => "required|numeric",
            "type" => ["required", Rule::in($type)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "images" => "required|array",
            "images.*" => "image|mimes:jpg,jpeg,png",
            "status_of_materail" => ["required", Rule::in($status_of_materail)],
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $governorate_id = Governorate::where('uuid', $request->governorate_uuid)->value('id');

        $Olddonation = Inkind_donation::where('user_id', Auth::user()->id)
        ->where('status', 'لم يتم استلامه بعد')->first();

        if( $Olddonation ){
            return $this->requiredField('لا يمكنك التبرع حتى تسلم ما تبرعت به مسبقا');
        }else{
        if ($request->hasFile('images')) {
            $images = $this->upload_files($request->file('images'),'inkinds/images');
        }


        $donation = Inkind_donation::create([
            'uuid' => Str::uuid(),
            'governorate_id' => $governorate_id,
            'user_id' => Auth::user()->id,
            'name_of_material' => $request->name_of_material,
            'amount' => $request->amount,
            'type' => $request->type,
            'on_the_other_hand' => $request->on_the_other_hand,
            'status_of_materail' => $request->status_of_materail,
            'status' => 'لم يتم استلامه بعد',
            'images' => $images
        ]);

        return $this->apiResponse(Inkind_donationResource::make($donation));
        }

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
}

    public function update( Request $request, $uuid){

        $donation = Inkind_donation::where('uuid', $uuid)->first();
        $status = ['تم استلامه','لم يتم استلامه بعد'];

        $validate = Validator::make($request->all(), [
            "status" => ["required", Rule::in($status)]
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }
        $data =[
            'status' => $request->status
        ];
        $donation->update($data);
        return $this->apiResponse(Inkind_donationResource::make($donation));
    }

    public function index(){
        $donations = Inkind_donation::all();

        if( $donations ){
        $donations = Inkind_donationResource::collection($donations);
        return $this->apiResponse( $donations );
        }
        else{
            return $this->apiResponse([]);
        }
    }

    public function get_type(){
    try{
        /*$type = Inkind_donation::whereNotNull('type')->pluck('type');
        $onTheOtherHand = Inkind_donation::whereNotNull('on_the_other_hand')->pluck('on_the_other_hand');
        $allData = $type->merge($onTheOtherHand)->unique() ->values();*/
        $type = ['أثاث' ,'أدوات منزلية', 'أجهزة طبية', 'أجهزة إلكترونية', 'ملابس', 'أدوات مدرسية', 'غير ذلك'];
        return $this->apiResponse($type);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function get_status(){
    try{
        $status = ['تم استلامه','لم يتم استلامه بعد'];
        return $this->apiResponse($status);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function get_status_of_materail(){
    try{
        $status = ['جديدة','مستعملة'];
        return $this->apiResponse($status);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function filter(Request $request){
    try{
    $donations = Inkind_donation::query()
    ->when($request->governorate_uuid, function ($q) use ($request) {
            $q->where('governorate_uuid', $request->governorate_uuid);
        })
        ->when($request->type, function ($q) use ($request) {
            $q->where('type', $request->type);
        })
        ->when($request->status, function ($q) use ($request) {
            $q->where('status', $request->status);
        })
        ->when($request->status_of_materail, function ($q) use ($request) {
            $q->where('status_of_materail', $request->status_of_materail);
        })
        ->get();

    return $this->apiResponse(Inkind_donationResource::collection($donations));
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }}

    public function searchByname( Request $request){
        try{
        $validate = Validator::make($request->all(), [
       "name" => "string|regex:/^[\p{Arabic}\s]+$/u" ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $donaters = Inkind_donation::whereHas('user', function ($query) use ($request) {
        $query->where('name', 'LIKE', '%' . $request->name . '%');
        })->get();

        if( $donaters->isNotEmpty() ){
        $donaters = Inkind_donationResource::collection( $donaters );
        return $this->apiResponse($donaters);
        }
        else{
            return $this->apiResponse([]);
        }
        } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
        }
    }


}
