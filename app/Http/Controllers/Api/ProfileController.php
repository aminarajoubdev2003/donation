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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $inkindDonations = Inkind_donation::where('user_id',Auth::user()->id)->
        where('status','تم استلامه')->count();
        $count = Donation::where('user_id',Auth::user()->id)->distinct('campaign_id')
        ->count('campaign_id');

        return $this->apiResponse([
            'inkind_donations_count' => $inkindDonations,
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

    public function updatePass( Request $request)
    {

        try{
        $validate = Validator::make($request->all(),[
            'oldpassword' => ['required','min:8'],
            'newpassword' => 'required|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]+$/',
        ],[
            'oldpassword.required' => 'كلمة المرور الحالية مطلوبة',
            'oldpassword.min' => 'كلمة المرور الحالية يجب أن تكون 8 أحرف على الأقل',
            'newpassword.required' => 'كلمة المرور الجديدة مطلوبة',
            'newpassword.min' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل',
            'newpassword.confirmed' => 'تأكيد كلمة المرور الجديدة غير متطابق',
            'newpassword.regex' => 'كلمة المرور الجديدة يجب أن تحتوي على حرف ورقم على الأقل',
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $user = Auth::user();

        if (!Hash::check($request->oldpassword, $user->password)) {
            return $this->requiredField('كلمة المرور الحالية غير صحيحة');
        }

        if (Hash::check($request->newpassword, $user->password)) {
            return $this->requiredField('لا يمكنك استخدام كلمة المرور القديمة نفسها');
        }

        $user->update([
           'password' => Hash::make($request->newpassword)
        ]);

        return $this->apiResponse(UserResource::make($user));

        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

}
