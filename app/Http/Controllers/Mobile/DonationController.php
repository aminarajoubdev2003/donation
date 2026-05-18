<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonationResource;
use App\Http\Resources\ImageResource;
use App\Http\traits\GeneralTrait;
use App\Http\traits\UploadTrait;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DonationController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function donate_directly( Request $request)
    {
        try{

        $validate = Validator::make($request->all(),[
            "campaign_uuid" => "required|string|exists:campaigns,uuid",
            "contribution_amount" => "required|numeric",
            "contribution_details" => "required|string|regex:/^[^\p{Latin}]+$/u",
            "image" => "required|image|mimes:jpg,jpeg,png",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $campaign_id = Campaign::where('uuid', $request->campaign_uuid)->value('id');
        $image = $this->upload_file($request->file('image'),'donations/images');

        $donation = Donation::create([
            'uuid' => Str::uuid(),
            'user_id' => Auth::user()->id,
            'campaign_id' => $campaign_id,
            'contribution_amount' => $request->contribution_amount,
            'contribution_details' => $request->contribution_details,
            'donate_directly' => 1,
            'image' => $image,
            'status'=> 'قيد التدقيق'
        ]);
        return $this->apiResponse( DonationResource::make($donation));
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function pledge_to_donate( Request $request)
    {
        try{
        $validate = Validator::make($request->all(),[
            "campaign_uuid" => "required|string|exists:campaigns,uuid",
            "contribution_amount" => "required|numeric",
            "contribution_details" => "required|string|regex:/^[^\p{Latin}]+$/u"
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $campaign_id = Campaign::where('uuid', $request->campaign_uuid)->value('id');

        $donation = Donation::create([
            'uuid' => Str::uuid(),
            'user_id' => Auth::user()->id,
            'campaign_id' => $campaign_id,
            'contribution_amount' => $request->contribution_amount,
            'contribution_details' => $request->contribution_details,
            'pledge_to_donate' => 1,
            'status'=> 'قيد التدقيق'
        ]);
        return $this->apiResponse(DonationResource::make($donation));

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);}
    }

    public function donate_for_pledge( Request $request, $donation_uuid)
    {
        try{
        $validate = Validator::make($request->all(),[
            "image" => "required|image|mimes:jpg,jpeg,png",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $donation = Donation::where('uuid', $donation_uuid)->first();
        $image = $this->upload_file($request->file('image'),'donations/images');

        $donation->update([
           'image' => $image,
        ]);
        return $this->apiResponse(DonationResource::make($donation));

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    // عرض QR
    public function showQR()
    {
    try{

    $qrData = "shamcash://pay?to=014ed0aaa36700fc36e139f272dddfca";
    return $this->apiResponse($qrData);

    }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }


    public function verify(Request $request, $donation_uuid)
    {
        try{
        $status = ['متوافق', 'غير متوافق'];

        $request->validate([
            "status" => ["required", Rule::in($status)]
        ]);

        $donation = Donation::where('uuid', $donation_uuid)->first();

        $donation->update([
            'status' => $request->status
        ]);

        return $this->apiResponse( DonationResource::make($donation));
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function show_img( $donation_uuid )
    {
        try{

        $image = Donation::where('uuid', $donation_uuid)->value('image');

        return $this->apiResponse( new ImageResource([
            'index' => 0,
            'path' => $image
            ]),);

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }
}
