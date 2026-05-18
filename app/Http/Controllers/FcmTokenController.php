<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
   public function saveFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string'
    ]);

    $user = auth()->user();

    $user->update([
        'fcm_token' => $request->fcm_token
    ]);

    return response()->json([
        'message' => 'تم حفظ التوكن بنجاح'
    ]);
}
}
