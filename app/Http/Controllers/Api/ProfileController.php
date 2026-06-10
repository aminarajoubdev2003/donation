<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Donater_ShowResource;
use App\Http\Resources\Inkind_donationResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Donation;
use App\Models\Inkind_donation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function getUser( ){
    try{
        $user = User::where('uuid',Auth::user()->uuid)->firstOrFail();
        return $this->apiResponse( UserResource::make($user) );
        } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function getDonations( ){
    try{
        $donations = Donation::where('user_id',Auth::user()->id)->get();

        if($donations->isNotEmpty()){
        return $this->apiResponse( Donater_ShowResource::collection($donations));
        }else{
            return $this->apiResponse( [] );
        }
        } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function getInkindDonations( ){
    try{
        $donations = Inkind_donation::where('user_id',Auth::user()->id)->get();

        if($donations->isNotEmpty()){
        return $this->apiResponse( Inkind_donationResource::collection($donations));
        }else{
            return $this->apiResponse( [] );
        }
        } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function getStatistics( ){
    try{
        $total = Donation::where('user_id',Auth::user()->id)->sum('usd_amount');
        $count = Donation::where('user_id',Auth::user()->id)->distinct('campaign_id')
        ->count('campaign_id');

        return $this->apiResponse([
            'total' => $total.' '.'%',
            'campaigns_count' => $count
        ]);

        } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function changeProfile( Request $request)
    {
        try{
        $validate = Validator::make($request->all(),[
            "profile" => "required|image|mimes:jpg,jpeg,png",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $user = Auth::user();
        if ($user->profile) {
            $this->delete_file($user->profile);
        }
        $profile = $this->upload_file($request->file('profile'),'users/profiles');

        $user->update([
           'profile' => $profile,
        ]);
        return $this->apiResponse(UserResource::make($user));

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

}
