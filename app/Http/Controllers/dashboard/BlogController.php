<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function store(Request $request)
    {
    try {

        $category = ['أخبار المشاريع','حملات جديدة','تقارير التوزيع','قصص نجاح',
        'تنبيهات عاجلة','فعاليات','شركات و منظمات'];

        $validate = Validator::make($request->all(), [
            "title" =>"required|string|min:3|max:200|regex:/^[\p{Arabic}\s]+$/u",
            "category" => ["required", Rule::in($category)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "images" => "required|array",
            "images.*" => "image|mimes:jpg,jpeg,png",
            "excerpt" =>"required|string|min:3|max:500|regex:/^[\p{Arabic}\s]+$/u",
            "content" =>"required|string|regex:/^[\p{Arabic}\s]+$/u",
        ],[
            '.required' => 'المحافظة مطلوبة',
            '.exists' => 'المحافظة المحددة غير موجودة',
            '.required' => 'اسم المادة مطلوب',
            '.string' => 'اسم المادة يجب أن يكون نصًا',
            '.min' => 'اسم المادة يجب ألا يقل عن 3 أحرف',
            '.max' => 'اسم المادة يجب ألا يزيد عن 100 حرف',
            '.regex' => 'اسم المادة يجب أن يحتوي على أحرف عربية فقط',
            '.required' => 'الكمية مطلوبة',
            '.numeric' => 'الكمية يجب أن تكون رقمًا',
            'type.required' => 'نوع المادة مطلوب',
            'type.in' => 'نوع المادة المحدد غير صالح',
            'on_the_other_hand.string' => 'حقل أخرى يجب أن يكون نصًا',
            'on_the_other_hand.max' => 'حقل أخرى يجب ألا يزيد عن 20 حرف',
            'on_the_other_hand.regex' => 'حقل أخرى يجب أن يحتوي على أحرف عربية فقط',
            'images.required' => 'الصور مطلوبة',
            'images.array' => 'الصور يجب أن تكون على شكل مصفوفة',
            'images.*.image' => 'يجب أن يكون الملف صورة',
            'images.*.mimes' => 'صيغة الصورة يجب أن تكون jpg أو jpeg أو png',
            'status_of_materail.required' => 'حالة المادة مطلوبة',
            'status_of_materail.in' => 'حالة المادة المحددة غير صالحة',
            'delivery_method.required' => 'طريقة التسليم مطلوبة',
            'delivery_method.in' => 'طريقة التسليم المحددة غير صالحة',
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $governorate_id = Governorate::where('uuid', $request->governorate_uuid)->value('id');

        if ($request->hasFile('images')) {
            $images = $this->upload_files($request->file('images'),'inkinds/images');
        }

        $donation = Inkind_donation::create([
            'uuid' => Str::uuid(),
            'governorate_id' => $governorate_id,
            'user_id' => Auth::user()->id,
            'name_of_material' => $request->name_of_material,
            'amount' => $request->amount,
            'type' => $request->type,
            'on_the_other_hand' => $request->on_the_other_hand,
            'status_of_materail' => $request->status_of_materail,
            'delivery_method' => $request->delivery_method,
            'status' => 'لم يتم استلامه بعد',
            'images' => $images
        ]);

        return $this->apiResponse(Inkind_donationResource::make($donation));

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
}

}
