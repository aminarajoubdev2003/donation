<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCampaignResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\ProjectResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Campaign;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CampaignApiController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function index(){
    try{
        $campaigns = Campaign::all();
        return $this->apiResponse( ApiCampaignResource::collection($campaigns) );

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
        ]);
    $campaigns = Campaign::query()

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

    return $this->apiResponse(ApiCampaignResource::collection($campaigns));
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

    public function get_status(){
    try{
        $status = ['جديدة','نشطة','متوقفة','مكتملة','منتهية'];
        return $this->apiResponse($status);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function show( $uuid ){
    try{
        $campaign = Campaign::with('projects')->where('uuid', $uuid)->firstOrFail();
        $campaign->refreshStatus();
        return $this->apiResponse( ApiCampaignResource::make($campaign) );
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function getProjects(){
    try{
        $projects = Project::all();

        if( $projects ){
        $projects = ProjectResource::collection($projects);
        return $this->apiResponse( $projects );
        }
        else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }


}
