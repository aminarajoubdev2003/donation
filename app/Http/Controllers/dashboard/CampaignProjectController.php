<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Campaign;
use App\Models\Campaign_project;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CampaignProjectController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function store( Request $request , $uuid){
    try {
        $validate = Validator::make($request->all(),[
            "project_uuid" => "array",
            "project_uuid.*" => "required|string|exists:projects,uuid",
        ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{

        $campaign = DB::transaction(function () use ($request,$uuid) {

        $campaign = Campaign::where('uuid', $uuid)->firstOrFail();

            if ($request->filled('project_uuid')) {

                $projectIds = Project::whereIn('uuid', $request->project_uuid)
                ->pluck('id');

                $usedProjects = DB::table('campaign_projects')
                    ->join('campaigns', 'campaign_projects.campaign_id', '=', 'campaigns.id')
                    ->whereIn('campaign_projects.project_id', $projectIds)
                    ->where('campaigns.status', '!=', 'ملغاة')
                    ->pluck('campaign_projects.project_id');

                if ($usedProjects->isNotEmpty()) {
                    return $this->apiResponse(null,false,'المشاريع التي تضيفها إلى هذه الحملة موجودة في حملات أخرى',400);
                }
                $campaign->projects()->attach($projectIds);
            }

            return $campaign;
        });
        return $this->apiResponse(CampaignResource::make($campaign));
        }

    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function delete( $uuidc,$uuidp ){
        try{
        $campaign_id = Campaign::where('uuid',$uuidc)->value('id');
        $project_id = Project::where('uuid', $uuidp)->value('id');

        $campaign_project = Campaign_project::where('campaign_id' ,$campaign_id)
        ->where('project_id' ,$project_id )->firstOrFail();

        if( $campaign_project->delete() ){
            return $this->apiResponse('the project is deleted from the campaign');
        }else{
            return $this->apiResponse(null, false, 'Failed to delete project', 400);
        }
        }catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function restore( $uuidc,$uuidp ){
        try{
        $campaign_id = Campaign::where('uuid',$uuidc)->value('id');
        $project_id = Project::where('uuid', $uuidp)->value('id');

        $campaign_project = Campaign_project::withTrashed()->where('campaign_id' ,$campaign_id)
        ->where('project_id' ,$project_id )->firstOrFail();


        if($campaign_project->restore()){
            return $this->apiResponse('the project is restored from the campaign');
        }else{
            return $this->apiResponse(null, false, 'Failed to restore project', 400);
        }
        } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }

    }
}
