<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\PendingResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Detail;
use App\Models\Pending;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PendingController extends Controller
{
    use GeneralTrait;

    public function store(Request $request)
    {
    try {
        $currentYear = now()->year;
        $previousYear = now()->subYear()->year;

        $validate = Validator::make($request->all(), [
        "detail_uuid" => ["required","string","exists:details,uuid"],
        "cost" => "required|numeric",
        "pending_date" => ["required","date","before_or_equal:today",
        function ($attribute, $value, $fail) use ($currentYear, $previousYear) {
            $year = Carbon::parse($value)->year;
            if ( $year != $currentYear && $year != $previousYear ) {
                $fail('يجب أن يكون التاريخ ضمن السنة الحالية أو السنة السابقة فقط.');
            }
        }
        ],
        "paid_amount" => "required|numeric",
        "remaining_amount" => "required|numeric"
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $detail_id = Detail::where('uuid', $request->detail_uuid)->value('id');
        $exists = Pending::where('detail_id',$detail_id)->where('pending_date',$request->pending_date)->exists();

        if ($exists) {
        return $this->requiredField( 'لا يمكن إضافة نفس التفصيل بنفس تاريخ الدفع أكثر من مرة.');
        }
        else{
        $pending = Pending::create([
            'uuid' => Str::uuid(),
            'detail_id' => $detail_id,
            'pending_date' => $request->pending_date,
            'paid_amount' => $request->paid_amount,
            'cost' => $request->cost,
            'remaining_amount' => round($request->cost - $request->paid_amount,2)
        ]);
        return $this->apiResponse(PendingResource::make($pending));
        }

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }
}
