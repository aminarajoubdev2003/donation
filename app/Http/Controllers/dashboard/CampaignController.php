<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\ProjectResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Campaign;
use App\Models\Campaign_project;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function store(Request $request)
    {
    try {

        $validate = Validator::make($request->all(),[
            "name" => "required|string|min:3|max:100|unique:campaigns,name|regex:/^[\p{Arabic}\s]+$/u",
            "target_amount" => "required|numeric",
            "start_date" => "required|date|after:today",
            "end_date" => "required|date|after:start_date",
            "start_time" => "required|date_format:H:i",
            "end_time" => "required|date_format:H:i",
            "purposes" => "required|string|regex:/^[\p{Arabic}\s0-9\p{P}\p{S}]+$/u",
            "image" => "required|image|mimes:jpg,jpeg,png",
        ],[
            'start_time.date_format' => 'تنسيق وقت البداية غير صحيح.',
            'end_time.date_format'   => 'تنسيق وقت النهاية غير صحيح.',
            'start_date.after'       =>' تاريخ البداية غير صالح.',
            'end_date.after'         => ' تاريخ النهاية غير صالح.',
            'name.regex'            => 'هذه الصيغة غير صالحة'
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        if ($request->hasFile('image')) {
            $image = $this->upload_file($request->file('image'),'campaigns/images');
        }


        $campaign = Campaign::create([
                'uuid' => Str::uuid(),
                'name' => $request->name,
                'target_amount' => $request->target_amount,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'purposes' => $request->purposes,
                'image' => $image
        ]);

        return $this->apiResponse(CampaignResource::make($campaign) );
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }



    public function update(Request $request, $uuid, $uuidP)
    {
    try {

        $campaign = Campaign::where('uuid', $uuid)->firstOrFail();

        $validate = Validator::make($request->all(),[
            "name" => "string|min:3|max:100|unique:campaigns,name,".$campaign->id."|regex:/^[\p{Arabic}\s]+$/u",
            "target_amount" => "numeric",
            "start_date" => "date|after:today",
            "end_date" => "date|after:start_date",
            "start_time" => "date_format:H:i",
            "end_time" => "date_format:H:i",
            "purposes" => "string|regex:/^[\p{Arabic}\s0-9\p{P}\p{S}]+$/u",
            "image" => "image|mimes:jpg,jpeg,png",
            "project_uuid" => "array",
            "project_uuid.*" => "nullable|string|exists:projects,uuid",
        ],[
            'start_time.date_format' => 'تنسيق وقت البداية غير صحيح.',
            'end_time.date_format'   => 'تنسيق وقت النهاية غير صحيح.',
            'start_date.after'       =>' تاريخ البداية غير صالح.',
            'end_date.after'         => ' تاريخ النهاية غير صالح.',
            'name.regex'            => 'هذه الصيغة غير صالحة'
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        if( $campaign->status == 'جديدة'){
        if ($request->hasFile('image')) {
            if ($campaign->image) {
                $this->delete_file($campaign->image);
            }
            $image = $this->upload_file($request->file('image'), 'campaigns/images');
        }else{
            $image = $campaign->image;
        }

        if ($request->filled('project_uuid')) {
        $newProjectIds = Project::whereIn('uuid',$request->project_uuid)->pluck('id')->toArray();

        $currentProjectIds = Campaign_project::where('campaign_id',$campaign->id)
        ->whereNull('deleted_at')->pluck('project_id')->toArray();

        $toDelete = array_diff($currentProjectIds,$newProjectIds);

        Campaign_project::where('campaign_id', $campaign->id)
        ->whereIn('project_id', $toDelete)
        ->delete();

        // المشاريع الجديدة أو المحذوفة سابقاً
        foreach ($newProjectIds as $projectId) {
        $pivot = Campaign_project::withTrashed()->where('campaign_id', $campaign->id)
        ->where('project_id', $projectId)->first();

        if ($pivot) {
            if ($pivot->trashed()) {
                $pivot->restore();
            }
        } else {
            Campaign_project::create([
                'uuid' => Str::uuid(),
                'campaign_id' => $campaign->id,
                'project_id' => $projectId,
            ]);
        }
    }}
    $campaign->update([
        'name' => $request->name,
        'target_amount' => $request->target_amount,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'purposes' => $request->purposes,
        'image' => $image,
    ]);
    return $this->apiResponse(CampaignResource::make($campaign));
    }else{
        return $this->requiredField('لا يمكن تعديل حملة .'. $campaign->status);
    }
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function index(){
    try{
        $campaigns = Campaign::all();
        return $this->apiResponse( CampaignResource::collection($campaigns) );
        } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function show( $uuid ){
    try{
        $campaign = Campaign::with('projects')->where('uuid', $uuid)->firstOrFail();
        $campaign->refreshStatus();
        return $this->apiResponse( CampaignResource::make($campaign) );
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }


    public function searchByname( Request $request){
    try{
        $validate = Validator::make($request->all(), [
        "name" => "string|regex:/^[\p{Arabic}\s]+$/u" ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $campaigns = Campaign::where('name', 'LIKE', '%' . $request->name . '%')->get();

        if( $campaigns->isNotEmpty() ){
        $campaign = CampaignResource::collection($campaigns);
        return $this->apiResponse($campaign);
        }
        else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function filter(Request $request){
    try{
        $request->validate([
        'status' => ['nullable', 'array'],
        'status.*' => [
        Rule::in(['جديدة','نشطة','متوقفة','مكتملة', 'منتهية'])],
        'name' => ['nullable', 'string', 'regex:/^[\p{Arabic}\s]+$/u'],
        ]);
    $campaigns = Campaign::query()
    ->when($request->name, function ($q) use ($request) {
            $q->where('name', 'LIKE', '%' . $request->name . '%');
        })

    ->when($request->governorate_uuid, function ($q) use ($request) {
            $q->whereHas('projects.district.city.governorate', function ($q2) use ($request) {
                $q2->where('governorates.uuid', $request->governorate_uuid);
            });
        })
        ->when($request->city_uuid, function ($q) use ($request) {
            $q->whereHas('projects.district.city', function ($q2) use ($request) {
                $q2->where('cities.uuid', $request->city_uuid);
            });
        })
        ->when($request->district_uuid, function ($q) use ($request) {
            $q->whereHas('projects.district', function ($q2) use ($request) {
                $q2->where('district.uuid', $request->district_uuid);
            });
        })
        ->when($request->project_uuid, function ($q) use ($request) {
            $q->whereHas('projects', function ($q2) use ($request) {
                $q2->where('projects.uuid', $request->project_uuid);
            });
        })
        ->when($request->status, function ($q) use ($request) {
           if (is_array($request->status)) {
                $q->whereIn('status', $request->status);
            } else {
                $q->where('status', $request->status);
            }
        })
        ->get();

    return $this->apiResponse(CampaignResource::collection($campaigns));
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function delete( $uuid ){
    try{
        $campaign = Campaign::where('uuid', $uuid)->firstOrFail();

        if( $campaign->status == 'جديدة' || $campaign->status == 'ملغاة'
        || $campaign->status == 'متوقفة'){
            $campaign->delete();
           return $this->index();
        }else{
            return $this->requiredField('لا يمكن حذف حملة .'. $campaign->status);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }


    public function restore( $uuid ){
    try{
        $campaign = Campaign::withTrashed()
        ->where('uuid', $uuid)
        ->firstOrFail();

        if($campaign->restore()){
            return $this->show( $uuid);
        }else{
            return $this->apiResponse(null, false, 'Failed to restore campaign', 400);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function stop( $uuid ){
    try{
        $campaign = Campaign::with('projects')->where('uuid', $uuid)->firstOrFail();
        $campaign->update([
            'status' => 'متوقفة',
        ]);
        return $this->apiResponse( CampaignResource::make($campaign) );
        } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function appeal( $uuid ){
    try{
        $campaign = Campaign::with('projects')->where('uuid', $uuid)->firstOrFail();
        $campaign->update([
            'status' => 'جديدة',
        ]);
        return $this->apiResponse( CampaignResource::make($campaign) );
        } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function get_status(){
    try{
        $status = ['جديدة','نشطة','متوقفة','مكتملة','منتهية'];
        return $this->apiResponse($status);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function deleted( ){
    try{
        $campaigns = Campaign::onlyTrashed()->get();

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
    public function getProjects(){
        try{
        $projects = Project::doesntHave('campaigns')->get();
         if( $projects ){
        $projects = ProjectResource::collection($projects);
        return $this->apiResponse( $projects );
        }
        else{
            return $this->apiResponse([]);
        }
    }catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

}
