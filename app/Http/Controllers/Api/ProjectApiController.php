<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectDetailResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectApiController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function getProjectDetail( $uuid ){
    try{
        //$project = Project::where('uuid', $uuid)->firstOrFail();
        $project = Project::with([ 'district', 'details.pendings', ])
        ->where('uuid', $uuid) ->firstOrFail();

        if( $project ){
        $project = ProjectDetailResource::make($project);
        return $this->apiResponse( $project );
        }
        else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }
}
