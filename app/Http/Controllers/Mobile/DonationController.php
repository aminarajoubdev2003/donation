<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\DonatersResource;
use App\Http\Resources\DonationResource;
use App\Http\Resources\ImageResource;
use App\Http\traits\GeneralTrait;
use App\Http\traits\UploadTrait;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\CurrencyConverterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
           "file" => "required|file|mimes:pdf|max:100",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $campaign = Campaign::where('uuid', $request->campaign_uuid)->firstOrFail();
        $file = $this->upload_file($request->file('file'),'donations/files');

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
            'file' => $file,
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
            "file" => "required|file|mimes:pdf|max:100",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $campaign = Campaign::where('uuid', $request->campaign_uuid)->firstOrFail();
        $file = $this->upload_file($request->file('file'),'donations/files');

        $donation = Donation::create([
            'uuid' => Str::uuid(),
            'user_id' => Auth::user()->id,
            'campaign_id' => $campaign->id,
            'contribution_amount' => $request->contribution_amount,
            'contribution_details' => $request->contribution_details,
            'currency_type' => $request->currency_type,
            'donate_directly' => 1,
            'image' => $file,
            'status'=> 'قيد التدقيق',
            'pending' => 1
        ]);
        return $this->apiResponse( DonationResource::make($donation));

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function updateFile( Request $request ,$uuid)
    {
        try{

        $validate = Validator::make($request->all(),[
            "file" => "required|file|mimes:pdf|max:100",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $donation = Donation::where('uuid', $uuid)->firstOrFail();

        if( $donation->user_id == Auth::user()->id){
        $oldFile = $this->delete_file( $donation->file );
        $newFile = $this->upload_file($request->file('file'),'donations/files');

        $data = [
            'file' => $newFile,
            'status'=> 'قيد التدقيق',
            'created_at' => Carbon::now()
        ];
        $donation->update($data);
        return $this->apiResponse( DonationResource::make($donation));
        }else{
            return $this->requiredField('هذا التبرع ليس لك');
        }

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function pledge_to_donate( Request $request)
    {
        try{
        $unaccepted = Donation::where('user_id',Auth::user()->id)->where('status','غير متوافق')
        ->exists();
        $currency_type = ['USD', 'SYP', 'EUR'];

        if( !$unaccepted )
        {
        $invalidStatuses = ['مسودة','ملغاة','متوقفة','منتهية'];
        $validate = Validator::make($request->all(),[
            "campaign_uuid" => "required|string|exists:campaigns,uuid",
            "contribution_amount" => "required|numeric",
            "contribution_details" => "string|regex:/^[^\p{Latin}]+$/u",
            "currency_type" => [ Rule::in($currency_type)]
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
            'currency_type' => $request->currency_type
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
            "file" => "required|file|mimes:pdf|max:100",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $donation = Donation::where('uuid', $donation_uuid)->first();
        $file = $this->upload_file($request->file('file'),'donations/files');

        $donation->update([
           'file' => $file,
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
        $reasons = ['عدم التطابق بين تاريخ الدفع وتاريخ الملف',
        'عدم التطابق بين المبلغ المدفوع والمبلغ الموجود داخل الملف'];

        $validate = Validator::make($request->all(),[
            "status" => ["required", Rule::in($status)],
            "reason" => ["nullable", Rule::in($reasons)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "remaining_amount" => "nullable|numeric"
        ]);
        if ($validate->fails()) {
            return $this->requiredField($request->validate->errors()->first());
        }

        $donation = Donation::where('uuid', $donation_uuid)->first();
        $donation->usd_amount = $this->currencyService->
        convertToUsd($donation->contribution_amount,$donation->currency_type);

        if( $request->status == 'متوافق'){
            $donation->update([
            'status' => $request->status,
            'pending' => 1,
            'remaining_amount' => 0
            ]);
        }
        elseif( $request->status == 'غير متوافق' && $request->reason == 'عدم التطابق بين تاريخ الدفع وتاريخ الملف'){
        $donation->update([
            'status' => $request->status,
            'pending' => 1,
            'reason' => $request->reason,
            'on_the_other_hand' => $request->on_the_other_hand,
            'remaining_amount' => 0
        ]);
        }
        else{
            $donation->update([
            'status' => $request->status,
            'pending' => 1,
            'reason' => $request->reason,
            'on_the_other_hand' => $request->on_the_other_hand,
            'remaining_amount' => $request->remaining_amount
            ]);
        }
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
            'file' => Storage::url($donation->file),
            'contribution_amount' => $donation->contribution_amount,
            'currency_type' => $donation->currency_type,
            'paiding_date' => Carbon::parse($donation->created_at)->format('d M Y')
        ]], 200);
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function getAmount( $donation_uuid )
    {
        try{

        $donation = Donation::where('uuid', $donation_uuid)->firstOrFail();

        if( $donation->user_id == Auth::user()->id){
        return response()->json([
        'status' => true,
        'data' => [
            'qrData' => 'shamcash://pay?to=014ed0aaa36700fc36e139f272dddfca',
            'campaign' => CampaignResource::make(Campaign::findOrFail($donation->campaign_id)),
            'remaining_amount' => $donation->remaining_amount,
            'currency_type' => $donation->currency_type,
        ]], 200);
        }
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function getPledge( $pledge_uuid )
    {
        try{

        $donation = Donation::where('uuid', $pledge_uuid)->firstOrFail();

        if( $donation->user_id == Auth::user()->id){
        return response()->json([
        'status' => true,
        'data' => [
            'qrData' => 'shamcash://pay?to=014ed0aaa36700fc36e139f272dddfca',
            'campaign' => CampaignResource::make(Campaign::findOrFail($donation->campaign_id)),
            'contribution_amount' => $donation->contribution_amount,
            'currency_type' => $donation->currency_type,
        ]], 200);
        }
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function getActiveCampaign(){
    try{
        $campaigns = Campaign::where('status','نشطة')->get();

        if( $campaigns ){
        $campaigns = CampaignResource::collection($campaigns);
        return $this->apiResponse( $campaigns );
        }
        else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }
}
