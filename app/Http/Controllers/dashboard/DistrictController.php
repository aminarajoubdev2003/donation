<?php

namespace App\Http\Controllers\dashboard;

use App\Models\City;
use App\Models\District;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Traits\UploadTrait;
use App\Http\Traits\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{
     use GeneralTrait , UploadTrait;

    public function store( Request $request ){
    try {
        $city_id = City::where('uuid', $request->city_uuid)->value('id');

        $validate = Validator::make($request->all(), [
            "district_name" => [
                "required",
                "string",
                "min:3",
                "max:100",
                "regex:/^[\p{Arabic}\s]+$/u",
                Rule::unique('districts', 'district_name')
                ->where(function ($query) use ($city_id) {
                return $query->where('city_id', $city_id);
                }),
            ],

                "city_uuid" => "string|exists:cities,uuid"
            ],[
                'district_name.unique' => 'هذه المنطقة موجودة مسيقا',
                'district_name.regex' =>  'صيغة حقل اسم المنطقة غير صالحة',
            ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{

        $district = District::create([
            'uuid' => Str::uuid(),
            'district_name' => $request->district_name,
            'city_id' => $city_id,
        ]);

        $district = DistrictResource::make($district);
        return $this->apiResponse($district);
        }

    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function update( Request $request , $uuid){
    try{
        $city_id = City::where('uuid', $request->city_uuid)->value('id');

        $validate = Validator::make($request->all(), [
            "district_name" => [
                "string",
                "min:3",
                "max:100",
                "regex:/^[\p{Arabic}\s]+$/u",
                Rule::unique('districts', 'district_name')
                ->where(function ($query) use ($city_id) {
                return $query->where('city_id', $city_id);
                }),
            ],

                "city_uuid" => "string|exists:cities,uuid"
            ],[
                'district_name.unique' => 'هذه المنطقة موجودة مسيقا',
                'district_name.regex' =>  'صيغة حقل اسم المنطقة غير صالحة',
            ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{

        $district = District::where('uuid',$uuid)->first();

        if (!$district) {
            return $this->apiResponse(null, false, "المدينة غير موجودة", 404);
        }

        $data = [
            'district_name' => $request->district_name,
            'city_id' => $city_id,
        ];

        $district->update($data);
        $district = DistrictResource::make($district);
        return $this->apiResponse($district);
        }
    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function index(){
        $districts = District::all();
        $districts = DistrictResource::collection($districts);

        return $this->apiResponse( $districts );
    }

    public function searchByname( Request $request){

        $validate = Validator::make($request->all(), [
        "district_name" => [ "string","min:3","max:100","regex:/^[\p{Arabic}\s]+$/u",] ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $districts = District::where('district_name', 'LIKE', '%' . $request->city_name . '%')->get();

        if( $districts->isNotEmpty() ){
        $district = DistrictResource::collection($districts);
        return $this->apiResponse($district);
        }else{
            return $this->apiResponse([]);
        }
    }

    public function show( $uuid ){
        $governorate = District::where('uuid', $uuid)->firstOrFail();
        return $this->apiResponse(DistrictResource::make($governorate));
    }

    public function filter(Request $request){

    $districts = District::query();

    // Filter by governorate UUID
    if ($request->governorate_uuid) {
        $districts->whereHas('city.governorate', function ($q) use ($request) {
            $q->where('uuid', $request->governorate_uuid);
        });
    }

    // Filter by city UUID
    if ($request->city_uuid) {
        $districts->whereHas('city', function ($q) use ($request) {
            $q->where('uuid', $request->city_uuid);
        });
    }

    $districts = $districts->get();

    $districts = DistrictResource::collection($districts);
    return $this->apiResponse( $districts );
    }
}
