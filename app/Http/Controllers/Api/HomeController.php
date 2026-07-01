<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Project;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use GeneralTrait;

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
