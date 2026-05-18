<?php

namespace App\Http\Controllers\mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inkind_donationResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Governorate;
use App\Models\Inkind_donation;
use Illuminate\Http\Request;
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
        $delivery_method = ['سأقوم بالتسليم','أحتاج فريق للتسليم'];

        $validate = Validator::make($request->all(), [
            "governorate_uuid" => "required|string|exists:governorates,uuid",
            "name_of_material" =>"required","string","min:3","max:100","regex:/^[\p{Arabic}\s]+$/u",
            "amount" => "required|numeric",
            "requirements" => "required|string|regex:/^[^\p{Latin}]+$/u",
            "type" => ["required", Rule::in($type)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "images" => "required|array",
            "images.*" => "image|mimes:jpg,jpeg,png",
            "status_of_materail" => ["required", Rule::in($status_of_materail)],
            "delivery_method" => ["required", Rule::in($delivery_method)],
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $governorate_id = Governorate::where('uuid', $request->governorate_uuid)->value('id');

        if ($request->hasFile('images')) {
            $images = $this->upload_files($request->file('images'),'projects/inkind_donations');
        }

        $donation = Inkind_donation::create([
            'uuid' => Str::uuid(),
            'governorate_id' => $governorate_id,
            'name_of_material' => $request->name_of_material,
            'amount' => $request->amount,
            'type' => $request->type,
            'on_the_other_hand' => $request->on_the_other_hand,
            'status_of_materail' => $request->status_of_materail,
            'delivery_method' => $request->delivery_method,
            'status' => $request->status,
            'images' => $images
        ]);

        return $this->apiResponse(Inkind_donationResource::make($donation));

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

}
