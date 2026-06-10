<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Jobs\SendOtpEmailJob;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class PasswordResetController extends Controller
{ use GeneralTrait;

public function sendOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);


    $otp = rand(100000, 999999);


    PasswordReset::updateOrCreate(
        [
            'email' => $request->email
        ],
        [
            'otp' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes(10),
        ]
    );
    SendOtpEmailJob::dispatch(
        $request->email,
        $otp
    );
    return response()->json([
        'message' => 'OTP sent successfully'
    ]);
}


public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required',
        'password' => 'required|min:8|confirmed',
    ]);


    $record = PasswordReset::where('email',$request->email)->first();


    if (!$record) {
        return $this->requiredField('OTP not found');
    }

    if (now()->greaterThan($record->expires_at)) {
        return $this->requiredField('OTP expired');
    }

    if (!Hash::check($request->otp, $record->otp)) {
        return $this->requiredField('Invalid OTP');
    }

    User::where('email',$request->email)->update([
        'password' => Hash::make( $request->password)
    ]);
    $record->delete();

    return $this->apiResponse('Password changed successfully');
}
}
