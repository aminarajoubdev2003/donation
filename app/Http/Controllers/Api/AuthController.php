<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\traits\GeneralTrait;
use App\Http\traits\UploadTrait;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function register( Request $request ){
    try{
        $validTypes = ['منظمات','رجال أعمال','فردي','أدمن'];

        $validate = Validator::make($request->all(),[
            "name" => "required|string|min:3|max:100|regex:/^[\p{Arabic}\s]+$/u",
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]+$/',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|digits:10|unique:users,phone|regex:/^(09)[0-9]{8}$/',
            'type' => 'required|in:' . implode(',', $validTypes),
            'contact_info' => 'nullable|string'
        ],[
            'name.required' => 'الاسم مطلوب',
            'name.regex' => 'الاسم يجب أن يحتوي على أحرف عربية فقط',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'password.regex' => 'كلمة المرور يجب أن تحتوي على حرف ورقم على الأقل',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.digits' => 'رقم الهاتف يجب أن يتكون من 10 أرقام',
            'phone.unique' => 'رقم الهاتف مستخدم مسبقاً',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ 09 ويتكون من 10 أرقام',
            'type.required' => 'نوع المستخدم مطلوب',
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
            'contact_info'=> $request->contact_info,
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
            'password' => 'required|min:8',
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

