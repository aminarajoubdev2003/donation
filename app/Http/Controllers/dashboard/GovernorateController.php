<?php

namespace App\Http\Controllers\dashboard;

use App\Models\Governorate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Traits\UploadTrait;
use App\Http\Traits\GeneralTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\GovernorateResource;

class GovernorateController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function store( Request $request ){
    try {
        $validate = Validator::make($request->all(),[
            "governorate_name" => "required|string|min:3|max:20|unique:governorates,governorate_name|regex:/^[\p{Arabic}\s]+$/u",
        ],[
            'governorate_name.unique' => 'هذه المحافظة موجودة مسيقا',
            'governorate_name.regex' =>  'صيغة حقل اسم المحافظة غير صالحة',
        ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{

        $governorate = Governorate::create([
            'uuid' => Str::uuid(),
            'governorate_name' => $request->governorate_name,
        ]);

        $governorate = GovernorateResource::make($governorate);
        return $this->apiResponse($governorate);
        }

    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }

    }

    public function update(Request $request, $uuid)
    {
    try {
        $validate = Validator::make($request->all(), [
            "governorate_name" => [
                "string",
                "min:3",
                "max:20",
                "regex:/^[\p{Arabic}\s]+$/u",
                Rule::unique('governorates', 'governorate_name')->ignore($uuid, 'uuid'),
            ],
        ], [
            'governorate_name.unique' => 'هذه المحافظة موجودة مسيقا',
            'governorate_name.regex' =>  'صيغة حقل اسم المحافظة غير صالحة',
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $governorate = Governorate::where('uuid', $uuid)->first();

        if (!$governorate) {
            return $this->apiResponse(null, false, "المحافظة غير موجودة", 404);
        }

        $data = [
            'governorate_name' => $request->governorate_name,
        ];

        $governorate->update($data);

        return $this->apiResponse(GovernorateResource::make($governorate));

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function index(){
        $governorates = Governorate::all();
        $governorates = GovernorateResource::collection($governorates);
        return $this->apiResponse( $governorates );
    }

    public function show( $uuid ){
        $governorate = Governorate::where('uuid', $uuid)->firstOrFail();
        return $this->apiResponse(GovernorateResource::make($governorate));
    }

    public function searchByname( Request $request){

        $validate = Validator::make($request->all(), [
        "governorate_name"=> [ "string","min:3","max:20","regex:/^[\p{Arabic}\s]+$/u",] ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $governorate = Governorate::where('governorate_name', $request->governorate_name)->firstOrFail();

        if( $governorate ){
        $governorate = GovernorateResource::make($governorate);
        return $this->apiResponse($governorate);
        }
        else{
            return $this->apiResponse([]);
        }
    }
}
