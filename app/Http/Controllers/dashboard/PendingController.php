<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetailResource;
use App\Http\Resources\PendingProjectResource;
use App\Http\Resources\PendingResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Detail;
use App\Models\Pending;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        "pending_date" => ["required","date","before_or_equal:today",
        function ($attribute, $value, $fail) use ($currentYear, $previousYear) {
            $year = Carbon::parse($value)->year;
            if ( $year != $currentYear && $year != $previousYear ) {
                $fail('يجب أن يكون التاريخ ضمن السنة الحالية أو السنة السابقة فقط.');
            }
        }
        ],
        "paid_amount" => "required|numeric",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $detail = Detail::where('uuid', $request->detail_uuid)->firstOrFail();
        $exists = Pending::where('detail_id',$detail->id)->where('pending_date',$request->pending_date)->exists();
        if ($exists) {
            return $this->requiredField( 'لا يمكن إضافة نفس التفصيل بنفس تاريخ الدفع أكثر من مرة.');
        }

        $exists_befor = Pending::where('detail_id',$detail->id)->exists();
        if($exists_befor){
            $cost = Detail::where('id',$detail->id)->with('latestPending')->firstOrFail();
            $latestPendingCost = $cost->latestPending?->remaining_amount;

            $pending = Pending::create([
            'uuid' => Str::uuid(),
            'detail_id' => $detail->id,
            'pending_date' => $request->pending_date,
            'paid_amount' => $request->paid_amount,
            'cost' => $latestPendingCost,
            'remaining_amount' => max(round($latestPendingCost - $request->paid_amount, 2), 0),
            ]);
            return $this->apiResponse(PendingResource::make($pending));
        }
        else{
        $pending = Pending::create([
            'uuid' => Str::uuid(),
            'detail_id' => $detail->id,
            'pending_date' => $request->pending_date,
            'paid_amount' => $request->paid_amount,
            'cost' => $detail->cost,
            'remaining_amount' => round($detail->cost - $request->paid_amount,2)
        ]);
        return $this->apiResponse(PendingResource::make($pending));
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function update(Request $request, $uuid)
    {
    try {

        $validate = Validator::make($request->all(), [
        "paid_amount" => "numeric",
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $pending = Pending::where('uuid', $uuid)->firstOrFail();
        $latestPending = $pending->detail->latestPending;
        if ($latestPending->id !== $pending->id) {
            return $this->requiredField('لا يمكن تعديل دفعة قديمة');
        }

        $today = Carbon::now()->toDateString();
        if ( $today != Carbon::parse($pending->created_at)->toDateString() ) {
            return $this->requiredField( 'انتهت مدة الصلاحية على التعديل');
        }

        $data = [
            'paid_amount' => $request->paid_amount,
            'remaining_amount' => round($pending->cost - $request->paid_amount,2)
        ];
        $pending->update($data);
        return $this->apiResponse(PendingResource::make($pending));

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function index(){
    try{
       $pendings = Pending::latest()->get();
       if( $pendings->isNotEmpty()){
            return $this->apiResponse( PendingResource::collection( $pendings ));
        }else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function filter(Request $request){
    try{
    $pendings = Pending::query()
    ->when($request->project_uuid, function ($q) use ($request) {
            $q->whereHas('detail.project', function ($q2) use ($request) {
                $q2->where('projects.uuid', $request->project_uuid);
            });
        })
        ->when($request->detail_uuid, function ($q) use ($request) {
            $q->whereHas('detail', function ($q2) use ($request) {
                $q2->where('details.uuid', $request->detail_uuid);
            });
        })
        ->when($request->pending_date, function ($q) use ($request) {
            $q->where('pending_date', $request->pending_date);
        })
        ->get();
    return $this->apiResponse(PendingResource::collection($pendings));
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function getProject(){
        $projects = Project::with(['details.latestPending'])
        ->whereHas('details.latestPending', function($query) {
            $query->where('remaining_amount', '>', 0);
        })
        ->get();
        if( $projects->isNotEmpty()){
            return $this->apiResponse(PendingProjectResource::collection($projects));
        }else{
            return $this->apiResponse([]);
        }
    }

    public function getDetails( $uuid ){
        $project_id = Project::where('uuid', $uuid)->value('id');
        $details = Detail::where('project_id',$project_id)
        ->whereHas('latestPending', function($query) {
            $query->where('remaining_amount', '>', 0);
        })
        ->get();
        if( $details->isNotEmpty()){
            return $this->apiResponse(DetailResource::collection($details));
        }else{
            return $this->apiResponse([]);
        }
    }
}
