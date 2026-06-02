<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Campaign_project;
use App\Models\Campaign;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

class CampaignProjectController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function addProjectToCampaign(Request $request, $uuid){
        try {

        $validate = Validator::make($request->all(), [
            "project_uuid" => "required|array",
            "project_uuid.*" => "required|string|exists:projects,uuid",
        ]);

        if ($validate->fails()) {
            return $this->apiResponse(null,false,$validate->errors()->first(),400);
        }

        DB::transaction(function () use ($request, $uuid) {
            $campaign = Campaign::where('uuid', $uuid)->firstOrFail();
            $projectIds = Project::whereIn('uuid',$request->project_uuid)->pluck('id');

            foreach ($projectIds as $projectId) {
                // هل يوجد ربط محذوف مع هذه الحملة؟
                $deletedRelation = Campaign_project::onlyTrashed()
                    ->where('campaign_id', $campaign->id)
                    ->where('project_id', $projectId)
                    ->first();
                if ($deletedRelation) {
                    $deletedRelation->restore();
                    continue;
                }
                // هل المشروع مرتبط حالياً بحملة أخرى غير ملغاة؟
                $usedProject = Campaign_project::query()
                    ->join(
                        'campaigns',
                        'campaign_projects.campaign_id',
                        '=',
                        'campaigns.id'
                    )
                    ->where('campaign_projects.project_id', $projectId)
                    ->whereNull('campaign_projects.deleted_at')
                    ->where('campaigns.status', '!=', 'ملغاة')
                    ->exists();

                if ($usedProject) {
                    throw new \RuntimeException(
                        'أحد المشاريع مرتبط بحملة أخرى '
                    );
                }

                // هل هو مرتبط بهذه الحملة مسبقاً؟
                $alreadyAttached = Campaign_project::where(
                    'campaign_id',
                    $campaign->id
                )
                ->where('project_id', $projectId)
                ->exists();

                if (!$alreadyAttached) {
                    $campaign->projects()->attach($projectId, [
                        'uuid' => Str::uuid()
                    ]);
                }
            }
        });

        $campaign = Campaign::where('uuid', $uuid)->firstOrFail();

        return $this->apiResponse(
            CampaignResource::make($campaign->fresh())
        );

    } catch (\RuntimeException $ex) {

        return $this->apiResponse(
            null,
            false,
            $ex->getMessage(),
            409
        );

    } catch (\Exception $ex) {

        return $this->apiResponse(
            null,
            false,
            $ex->getMessage(),
            500
        );
    }
    }


    public function addCampaignToProject(Request $request,$project_uuid)
    {
    try {

        $validate = Validator::make($request->all(), [
            'campaign_uuid' =>'required|string|exists:campaigns,uuid',
        ]);

        if ($validate->fails()) {

            return $this->apiResponse(null,false,$validate->errors()->first(),400);
        }

        $project = Project::where('uuid',$project_uuid)->firstOrFail();

        $campaign = Campaign::where('uuid', $request->campaign_uuid)->firstOrFail();

        DB::transaction(function () use ($project,$campaign) {

            $isUsed = DB::table('campaign_projects')

                ->join(
                    'campaigns',
                    'campaign_projects.campaign_id',
                    '=',
                    'campaigns.id'
                )

                ->where(
                    'campaign_projects.project_id',
                    $project->id
                )

                ->where(
                    'campaigns.status',
                    '!=',
                    'ملغاة'
                )

                ->exists();

            if ($isUsed) {

                throw new \RuntimeException(
                    'هذا المشروع مرتبط مسبقًا بحملة أخرى'
                );
            }

            $project->campaigns()->attach(
                $campaign->id,
                [
                    'uuid' => Str::uuid()
                ]);
        });

        return $this->apiResponse(CampaignResource::make($campaign));
    }

    catch (\RuntimeException $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),409);
    }

    catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),500);
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

    public function restore( $campaign_id, $project_id){
        try{

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
