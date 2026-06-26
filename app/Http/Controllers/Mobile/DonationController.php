<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonatersResource;
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
use App\Services\CurrencyConverterService;

class DonationController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function donate_directly( Request $request)
    {
        try{
        $unaccepted = Donation::where('user_id',Auth::user()->id)->where('status','غير متوافق')
        ->exists();
        if( !$unaccepted )
        {
        $currency_type = ['USD', 'SYP', 'EUR'];
        $invalidStatuses = ['جديدة','ملغاة','متوقفة','منتهية'];

        $validate = Validator::make($request->all(),[
            "campaign_uuid" => "required|string|exists:campaigns,uuid",
            "contribution_amount" => "required|numeric",
            "contribution_details" => "nullable|string|regex:/^[^\p{Latin}]+$/u",
            "currency_type" => [ Rule::in($currency_type)],
            "image" => "required|image|mimes:jpg,jpeg,png",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $campaign = Campaign::where('uuid', $request->campaign_uuid)->firstOrFail();
        $image = $this->upload_file($request->file('image'),'donations/images');

        if (!in_array($campaign->status, $invalidStatuses))
        {
        $donation = Donation::create([
            'uuid' => Str::uuid(),
            'user_id' => Auth::user()->id,
            'campaign_id' => $campaign->id,
            'contribution_amount' => $request->contribution_amount,
            'contribution_details' => $request->contribution_details,
            'currency_type' => $request->currency_type,
            'donate_directly' => 1,
            'image' => $image,
            'status'=> 'قيد التدقيق',
            'pending' => 1
        ]);
        return $this->apiResponse( DonationResource::make($donation));

        }else{
            return $this->requiredField('هذه الحملة لم تبدأ بعد  ');
        }
        }else{
            return $this->requiredField(' لديك تبرع غير مكتمل عليك تكملته قبل القيام بتبرع جديد');
        }
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function complete( Request $request)
    {
        try{
        $currency_type = ['USD', 'SYP', 'EUR'];
        $invalidStatuses = ['مسودة','ملغاة','متوقفة','منتهية'];

        $validate = Validator::make($request->all(),[
            "campaign_uuid" => "required|string|exists:campaigns,uuid",
            "contribution_amount" => "required|numeric",
            "contribution_details" => "nullable|string|regex:/^[^\p{Latin}]+$/u",
            "currency_type" => [ Rule::in($currency_type)],
            "image" => "required|image|mimes:jpg,jpeg,png",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $campaign = Campaign::where('uuid', $request->campaign_uuid)->firstOrFail();
        $image = $this->upload_file($request->file('image'),'donations/images');

        $donation = Donation::create([
            'uuid' => Str::uuid(),
            'user_id' => Auth::user()->id,
            'campaign_id' => $campaign->id,
            'contribution_amount' => $request->contribution_amount,
            'contribution_details' => $request->contribution_details,
            'currency_type' => $request->currency_type,
            'donate_directly' => 1,
            'image' => $image,
            'status'=> 'قيد التدقيق',
            'pending' => 1
        ]);
        return $this->apiResponse( DonationResource::make($donation));

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function pledge_to_donate( Request $request)
    {
        try{
        $unaccepted = Donation::where('user_id',Auth::user()->id)->where('status','غير متوافق')
        ->exists();
        if( !$unaccepted )
        {
        $invalidStatuses = ['مسودة','ملغاة','متوقفة','منتهية'];
        $validate = Validator::make($request->all(),[
            "campaign_uuid" => "required|string|exists:campaigns,uuid",
            "contribution_amount" => "required|numeric",
            "contribution_details" => "required|string|regex:/^[^\p{Latin}]+$/u"
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $campaign = Campaign::where('uuid', $request->campaign_uuid)->firstOrFail();

        if (!in_array($campaign->status, $invalidStatuses)){

        $donation = Donation::create([
            'uuid' => Str::uuid(),
            'user_id' => Auth::user()->id,
            'campaign_id' => $campaign->id,
            'contribution_amount' => $request->contribution_amount,
            'contribution_details' => $request->contribution_details,
            'pledge_to_donate' => 1,
            'status'=> 'قيد التدقيق',
        ]);
        return $this->apiResponse(DonationResource::make($donation));

        }else{
            return $this->requiredField('هذه الحملة لم تبدأ بعد  ');
        }
        }else{
            return $this->requiredField(' لديك تبرع غير مكتمل عليك تكملته قبل القيام بتبرع جديد');
        }
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
           'pending' => 1
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

    public function __construct(
        private CurrencyConverterService $currencyService
    ) {}

    public function verify(Request $request, $donation_uuid)
    {
        try{
        $status = ['متوافق', 'غير متوافق'];

        $request->validate([
            "status" => ["required", Rule::in($status)]
        ]);

        $donation = Donation::where('uuid', $donation_uuid)->first();
        $donation->usd_amount = $this->currencyService->
        convertToUsd($donation->contribution_amount,$donation->currency_type);

        $donation->update([
            'status' => $request->status,
            'pending' => 1,
        ]);

        return $this->apiResponse( DonatersResource::make($donation));
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function show_img( $donation_uuid )
    {
        try{

        $donation = Donation::where('uuid', $donation_uuid)->firstOrFail();

        return response()->json([
        'status' => true,
        'data' => [
            'image' => new ImageResource(['index' => 0,'path' => $donation->image]),
            'contribution_amount' => $donation->contribution_amount,
            'currency_type' => $donation->currency_type
        ]], 200);
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }
}
