<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\traits\UploadTrait;
use App\Http\traits\GeneralTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function register( Request $request ){
    try{
        $validTypes = ['منظمات','رجال أعمال','فردي'];

        $validate = Validator::make($request->all(),[
            "name" => "required|string|min:3|max:30|regex:/^[\p{Arabic}\s]+$/u",
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|digits:10|unique:users,phone|regex:/^(09)[0-9]{8}$/',
            'type' => 'required|in:' . implode(',', $validTypes),
            'contact_info' => 'nullable|string'
        ],[
            'email.unique' => 'هذا الايميل موجود مسبقا',
            'password.regex' => 'Password must contain at least one letter and one number.',
        ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{
            $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'type' => $request->type,
            'contact_info'=> $request->contact_info
        ]);
        }
        $data['user'] = $user;
        $data['token'] = $user->createToken('MyApp')->plainTextToken;
        return $this->apiResponse($data);

    } catch ( Exception $e){
        return $this->apiResponse(null, false, $e->getMessage(), 500);
    }
    }

    public function login( Request $request ) {
    try{
        $validatedData = Validator::make($request->all(),[
            'password' => 'required|min:8|confirmed',
            'email' => 'required|email',
        ]);

        if(!Auth::attempt(['password' => $request->password,'email' => $request->email ])){
            return $this->unAuthorizeResponse();
        }

        $user = Auth::user();

        $data['user'] = $user;
        $data['token'] = $user->createToken('MyApp')->plainTextToken;
        return $this->apiResponse($data);

    }catch( Exception $e){
        return $this->apiResponse(null,false,$e->getMessage(),500);
    }
    }

    public function logout()
    {
    try {
        $user = auth('sanctum')->user();

        if ($user) {
            $user->tokens()->delete();
            return $this->apiResponse([], true, null, 200);
        }else {
            return $this->unAuthorizeResponse();
        }

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function notifications(Request $request)
    {
    return $request->user()->notifications;
    }
}

