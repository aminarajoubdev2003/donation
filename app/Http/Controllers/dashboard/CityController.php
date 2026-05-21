<?php

namespace App\Http\Controllers\dashboard;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Traits\UploadTrait;
use App\Http\Traits\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function store( Request $request ){
    try {
        $governorate_id = Governorate::where('uuid', $request->governorate_uuid)->value('id');

        $validate = Validator::make($request->all(), [
            "city_name" => [
                "required",
                "string",
                "min:3",
                "max:20",
                "regex:/^[\p{Arabic}\s]+$/u",
                Rule::unique('cities', 'city_name')
                ->where(function ($query) use ($governorate_id) {
                return $query->where('governorate_id', $governorate_id);
                }),
            ],

                "governorate_uuid" => "required|string|exists:governorates,uuid"
            ],[
                'city_name.unique' => 'هذا الحي موجود مسيقا',
                'city_name.regex' =>  'صيغة حقل اسم الحي غير صالحة',
            ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{
        $governorate_id = Governorate::where('uuid' , $request->governorate_uuid)->value('id');

        $city = City::create([
            'uuid' => Str::uuid(),
            'city_name' => $request->city_name,
            'governorate_id' => $governorate_id,
        ]);

        $city = CityResource::make($city);
        return $this->apiResponse($city);
        }

    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function update( Request $request , $uuid){
    try{
        $governorate_id = Governorate::where('uuid', $request->governorate_uuid)->value('id');

        $validate = Validator::make($request->all(), [
            "city_name" => [
                "string",
                "min:3",
                "max:20",
                "regex:/^[\p{Arabic}\s]+$/u",
                Rule::unique('cities', 'city_name')
                ->where(function ($query) use ($governorate_id) {
                return $query->where('governorate_id', $governorate_id);
                }),
            ],

                "governorate_uuid" => "required|string|exists:governorates,uuid"
            ],[
                'city_name.unique' => 'هذا الحي موجود مسيقا',
                'city_name.regex' =>  'صيغة حقل اسم الحي غير صالحة',
            ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{
        $city = City::where('uuid',$uuid)->first();

        if (!$city) {
            return $this->apiResponse(null, false, "المدينة غير موجودة", 404);
        }

        $data = [
            'city_name' => $request->city_name,
            'governorate_id' => $governorate_id,
        ];

        $city->update($data);

        $city = CityResource::make($city);
        return $this->apiResponse($city);
        }
    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function index(){
        $cities = City::all();
        $cities = CityResource::collection($cities);
        return $this->apiResponse($cities);
    }

    public function show( $uuid ){
        $governorate = City::where('uuid', $uuid)->firstOrFail();
        return $this->apiResponse(CityResource::make($governorate));
    }

    public function searchByname( Request $request){

        $validate = Validator::make($request->all(), [
        "city_name" => [ "string","min:3","max:20","regex:/^[\p{Arabic}\s]+$/u",] ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $cities = City::where('city_name', $request->city_name)->get();

        if( $cities ){
        $cities = CityResource::collection($cities);
        return $this->apiResponse($cities);
        }
        else{
            return $this->apiResponse([]);
        }
    }

    public function filter( Request $request ){
        $validate = Validator::make($request->all(),[
            "governorate_uuid" => "string|exists:governorates,uuid"
        ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{
        $governorate_id = Governorate::where('uuid', $request->governorate_uuid)->value('id');
        $cities = City::where('governorate_id',$governorate_id)->get();

        if( $cities ){
        $cities = CityResource::collection($cities);
        return $this->apiResponse($cities);
        }
        else{
            return $this->apiResponse([]);
        }
        }
    }
}
