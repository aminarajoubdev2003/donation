<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetailResource;
use App\Http\Resources\ProjectResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Detail;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectMediaController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function uploadMedia( Request $request , $uuid){
    try {
        $validate = Validator::make($request->all(), [
            "cover_image" => "nullable|image|mimes:jpg,jpeg,png",
            "images" => "nullable|array",
            "images.*" => "image|mimes:jpg,jpeg,png",
            "videos" => "nullable|array",
            "videos.*" => "nullable|url",
        ]);


        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{

        $project = Project::where('uuid', $uuid)->firstOrFail();
        $data = [];

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->upload_file($request->file('cover_image'),'projects/cover_images');
        }

        if ($request->hasFile('images')) {
            $oldImages = $project->images ?? [];
            $newImages = $this->upload_files($request->file('images'),'projects/images');
            $data['images'] = array_merge($oldImages, $newImages);
        }

        if ($request->filled('videos')) {
            $oldVideos = $project->videos ?? [];
            $newVideos = $request->videos;
            $data['videos'] = array_merge($oldVideos, $newVideos);
        }

        if (!empty($data)) {
            $project->update($data);
        }
            return $this->apiResponse( new ProjectResource($project) );
        }

    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }


    public function delete_one( $uuid ){

        $project = Project::where('uuid', $uuid)->firstOrFail();
        $data = [];
        if ($project->cover_image) {
            $this->delete_file($project->cover_image);
            $data['cover_image'] = NULL;
        }
        if (!empty($data)) {
            $project->update($data);
            return $this->apiResponse( new ProjectResource($project) );
        }

    }

    public function deleteImageUsingModel($uuid, $index)
    {
    try {
        $project = Project::where('uuid', $uuid)->firstOrFail();

        // استخدام الدالة المساعدة من الموديل
        $project->removeImageByIndex($index);

        return $this->apiResponse( new ProjectResource($project) );

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function deleteVideoUsingModel($uuid, $index)
    {
    try {
        $project = Project::where('uuid', $uuid)->firstOrFail();

        // استخدام الدالة المساعدة من الموديل
        $project->removeVideoByIndex($index);

        return $this->apiResponse( new ProjectResource($project) );

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function addDetails( Request $request ,$uuid){
    try{
        $project_id = Project::where('uuid', $uuid)->value('id');

        $validate = Validator::make($request->all(),[
        "detail" => [
        "required",
        "string",
        "min:3",
        "max:100",
        "regex:/^[\p{Arabic}0-9\s\p{P}\p{S}]+$/u",
        Rule::unique('details', 'detail')
            ->where(function ($query) use ($project_id) {
                return $query->where('project_id',$project_id);
        })
        ],
            "cost" => "required|numeric",
        ],[
            'detail.unique' => 'تمت إضافة هذا التفصيل إلى المشروع مسبقا',
            'detail.regex' => 'هذه الصيغة غير صالحة'
        ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());

        }else{

            $detail = Detail::create([
            'uuid' => Str::uuid(),
            'project_id' => $project_id,
            'detail' => $request->detail,
            'cost' => $request->cost
            ]);

        $detail = DetailResource::make($detail);
        return $this->apiResponse($detail);

        }
    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function updateDetails( Request $request ,$uuidp, $uuid){
    try{
        $project_id = Project::where('uuid', $uuidp)->value('id');

        $validate = Validator::make($request->all(),[
        "detail" => [
        "string",
        "min:3",
        "max:100",
        "regex:/^[\p{Arabic}0-9\s\p{P}\p{S}]+$/u",
        Rule::unique('details', 'detail')
            ->where(function ($query) use ($project_id) {
                return $query->where('project_id',$project_id);
        })
        ],
            "cost" => "numeric",
        ],[
            'detail.unique' => 'تمت إضافة هذا التفصيل إلى المشروع مسبقا',
            'detail.regex' => 'هذه الصيغة غير صالحة'
        ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());

        }else{

            $data = [
            'detail' => $request->detail,
            'cost' => $request->cost
            ];

            $detail = Detail::where('uuid' , $uuid)->firstOrfail();
            $detail->update($data);

        $detail = DetailResource::make($detail);
        return $this->apiResponse($detail);

        }
    } catch (\Exception $ex){
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }

    public function all_details( $uuidp ){
    try{
        $project_id = Project::where('uuid',$uuidp)->value('id');
        $details = Detail::where( 'project_id', $project_id)->get();
        if( $details ){
            return $this->apiResponse( DetailResource::collection($details) );
        }else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function delete_detail( $uuidp,$uuid){
    try{
        $detail = Detail::where('uuid', $uuid)->firstOrFail();
        if( $detail->delete() ){
            return $this->all_details( $uuidp);
        }else{
            return $this->apiResponse(null, false, 'Failed to delete project', 400);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 400);
    }
    }

    public function restore_detail( $uuid ){
        $detail = Detail::withTrashed()
        ->where('uuid', $uuid)
        ->firstOrFail();

        if($detail->restore()){
            return $this->apiResponse( DetailResource::make($detail));
        }else{
            return $this->apiResponse(null, false, 'Failed to restore project', 400);
        }

    }
}

