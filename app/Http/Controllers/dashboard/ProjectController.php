<?php

namespace App\Http\Controllers\dashboard;

use App\Models\Project;
use App\Models\District;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Traits\UploadTrait;
use App\Http\Traits\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Campaign_project;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\campaign_project as CampaignProjectResource;

class ProjectController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function store(Request $request)
    {
    try {
        $district_id = District::where('uuid', $request->district_uuid)->value('id');
        $sectors = ['تعليمي', 'صحي', 'إغاثي', 'إعمار', 'غير ذلك'];
        $funding_sources = ['رجال أعمال', 'منظمات', 'تبرعات'];
        $status = ['متوقف','قيد التنفيذ','مكتمل','مخطط له'];

        $validate = Validator::make($request->all(), [
            "name" => [
            "required",
            "string",
            "min:3",
            "max:100",
            "regex:/^[\p{Arabic}\s]+$/u",
            Rule::unique('projects', 'name')
                ->where(function ($query) use ($district_id) {
                    return $query->where(
                        'district_id',
                        $district_id
                    );
                }),
            ],
            "district_uuid" => "required|string|exists:districts,uuid",
            "estimated_cost" => "required|numeric",
            "requirements" => "required|string|regex:/^[\p{Arabic}\s0-9\p{P}\p{S}]+$/u",
            "sector" => ["required", Rule::in($sectors)],
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "funding_source" => ["required", Rule::in($funding_sources)],
            "Implementing_party" => "required|string|min:3|max:50",
            "status" => ["required", Rule::in($status)],
            "progress_percentage" => "nullable|integer|min:0|max:100",
            "cover_image" => "required|image|mimes:jpg,jpeg,png",
        ],[
            'name.unique' => 'هذا المشروع موجود مسبقًا ضمن نفس المنطقة.',
        ]);

        if ($validate->fails()) {
            return $this->requiredField($validate->errors()->first());
        }

        $district_id = District::where('uuid', $request->district_uuid)->value('id');

        if ($request->hasFile('cover_image')) {
            $cover_image = $this->upload_file($request->file('cover_image'),'projects/cover_images');
        }

        if ( !$request->on_the_other_hand ){
            $on_the_other_hand = null;
        }

        $project = Project::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'district_id' => $district_id,
            'estimated_cost' => $request->estimated_cost,
            'requirements' => $request->requirements,
            'sector' => $request->sector,
            'on_the_other_hand' => $request->on_the_other_hand,
            'funding_source' => $request->funding_source,
            'Implementing_party' => $request->Implementing_party,
            'status' => $request->status,
            'progress_percentage' => $request->progress_percentage ?? 0,
            'cover_image' => $cover_image
        ]);

        return $this->apiResponse(ProjectResource::make($project));

    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
}


    public function update( Request $request , $uuid){
    try{
        $sectors = ['تعليمي', 'صحي', 'إغاثي', 'إعمار', 'غير ذلك'];
        $funding_sources = ['رجال أعمال', 'منظمات', 'تبرعات'];
        $status = ['متوقف','قيد التنفيذ','مكتمل','مخطط له'];

        $project = Project::where('uuid', $uuid)->firstOrFail();
        $district_id = District::where('uuid', $request->district_uuid)->value('id');

        $validate = Validator::make($request->all(), [
            "name" => [
            "required",
            "string",
            "min:3",
            "max:100",
            "regex:/^[\p{Arabic}\s]+$/u",
            Rule::unique('projects', 'name')
            ->where(function ($query) use ($request) {
            $district_id = District::where('uuid', $request->district_uuid)->value('id');
            return $query->where('district_id', $district_id);
            })->ignore($project->id),
            ],
            "district_uuid" => "string|exists:districts,uuid",
            "estimated_cost" => "numeric",
            "progress_percentage" => "nullable|integer|min:0|max:100",
            "requirements" => "string|regex:/^[\p{Arabic}\s0-9\p{P}\p{S}]+$/u",
            "sector" => [ Rule::in($sectors)],
            "funding_source" => [ Rule::in($funding_sources)],
            "Implementing_party" => "string|regex:/^[\p{Arabic}\s]+$/u|min:3|max:50",
            "on_the_other_hand" => "nullable|string|min:0|max:20|regex:/^[\p{Arabic}\s]+$/u",
            "status" => [ Rule::in($status)],
            "cover_image" => "nullable|image|mimes:jpg,jpeg,png",
            "images" => "nullable|array",
            "images.*" => "image|mimes:jpg,jpeg,png",
            "videos" => "nullable|array",
            "videos.*" => "nullable|url",
        ],[
            'name.unique' => 'هذا المشروع موجود مسبقًا ضمن نفس المنطقة.',
        ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }
        else{
            $district_id = District::where('uuid', $request->district_uuid)->value('id');
            $project = Project::where('uuid', $uuid)->firstOrFail();

            // تحديث الصورة الرئيسية
            if ($request->hasFile('cover_image')) {
            // حذف الصورة القديمة إذا وجدت
                if ($project->cover_image) {
                    $this->delete_file($project->cover_image);
                }
            $cover_image = $this->upload_file($request->file('cover_image'), 'projects/cover_images');
            }else{
                $cover_image = $project->cover_image;
            }

            // تحديث الصور المتعددة
            if ($request->hasFile('images')) {
            // حذف الصور القديمة إذا وجدت
                if ($project->images) {
                    $this->delete_files($project->images);
                }
            $images = $this->upload_files($request->file('images'), 'projects/images');
            }else{
                $images = $project->images;
            }

            // تحديث الفيديوهات
            if ($request->filled('videos')) {
            $videos = $request->videos;
            }else{
                $videos = $request->videos;
            }

            $data = [
            'name' => $request->name,
            'district_id' => $district_id,
            'estimated_cost' => $request->estimated_cost,
            'progress_percentage' => $request->progress_percentage ?? 0,
            'requirements' => $request->requirements,
            'sector' => $request->sector,
            'on_the_other_hand' => $request->on_the_other_hand,
            'funding_source' => $request->funding_source,
            'Implementing_party' => $request->Implementing_party,
            'status' => $request->status,
            'cover_image' => $cover_image,
            'images' => $images,
            'videos' => $videos
            ];

            $project->update($data);
            $project = ProjectResource::make($project);
            return $this->apiResponse( $project );
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function index(){
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

    public function filter(Request $request){
    try{
        $request->validate([
        'status' => ['nullable', 'array'],
        'status.*' => [
        Rule::in(['متوقف','قيد التنفيذ','مكتمل','مخطط له']),
        'name' => ['nullable', 'string', 'regex:/^[\p{Arabic}\s]+$/u']
        ]]);

    $projects = Project::query()
    ->when($request->name, function ($q) use ($request) {
            $q->where('name', 'LIKE', '%' . $request->name . '%');
        })
    ->when($request->governorate_uuid, function ($q) use ($request) {
            $q->whereHas('district.city.governorate', function ($q2) use ($request) {
                $q2->where('governorates.uuid', $request->governorate_uuid);
            });
        })
        ->when($request->city_uuid, function ($q) use ($request) {
            $q->whereHas('district.city', function ($q2) use ($request) {
                $q2->where('cities.uuid', $request->city_uuid);
            });
        })
        ->when($request->district_uuid, function ($q) use ($request) {
            $q->whereHas('district', function ($q2) use ($request) {
                $q2->where('districts.uuid', $request->district_uuid);
            });
        })
        ->when($request->sector, function ($q) use ($request) {
            $q->where('sector', $request->sector);
        })
        ->when($request->status, function ($q) use ($request) {
            if (is_array($request->status)) {
                $q->whereIn('status', $request->status);
            } else {
                $q->where('status', $request->status);
            }
        })
        ->when($request->progress_percentage, function ($q) use ($request) {
            $q->where('progress_percentage', $request->progress_percentage);
        })
        ->when($request->funding_source, function ($q) use ($request) {
            $q->where('funding_source', $request->funding_source);
        })
        ->get();

    return $this->apiResponse(ProjectResource::collection($projects));
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function searchByname( Request $request){
    try{
        $validate = Validator::make($request->all(), [
        "name"=> [ "string","regex:/^[\p{Arabic}\s]+$/u",] ]);

        if ($validate->fails()) {
        return $this->requiredField($validate->errors()->first());
        }

        $projects = Project::where('name', 'LIKE', '%' . $request->name . '%')->get();

        if($projects->isNotEmpty()){
        $project = ProjectResource::collection($projects);
        return $this->apiResponse($project);
        }
        else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function delete( $uuid ){
    try{
        $project = Project::where('uuid', $uuid)->firstOrFail();
        if( $project->delete() ){
            return $this->index();
        }else{
            return $this->apiResponse(null, false, 'Failed to delete project', 400);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function restore( $uuid ){
    try{
        $project = Project::withTrashed()
        ->where('uuid', $uuid)
        ->firstOrFail();

        if($project->restore()){
            return $this->show( $uuid);
        }else{
            return $this->apiResponse(null, false, 'Failed to restore project', 400);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function show( $uuid ){
    try{

        $project = Project::with(['details','campaigns'])->where('uuid', $uuid)->firstOrFail();
        return $this->apiResponse(CampaignProjectResource::make($project));
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function get_sector(){
    try{
        /*$sectors = Project::whereNotNull('sector')->pluck('sector');
        $onTheOtherHand = Project::whereNotNull('on_the_other_hand')->pluck('on_the_other_hand');
        $allData = $sectors->merge($onTheOtherHand)->unique() ->values();*/
        $sectors = Project::where('sector', '!=', 'غير ذلك')->whereNotNull('sector')
        ->distinct()->pluck('sector')->values();

        $onTheOtherHand = Project::whereNotNull('on_the_other_hand')->distinct()
        ->pluck('on_the_other_hand')->values();

        $allData = $sectors->merge($onTheOtherHand)->unique() ->values();

        return $this->apiResponse($allData);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }
    public function get_status(){
    try{
        $status = ['متوقف','قيد التنفيذ','مكتمل','مخطط له'];
        return $this->apiResponse($status);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }
    public function get_funding_source(){
    try{
        $funding_source = ['رجال أعمال', 'منظمات', 'تبرعات'];
        return $this->apiResponse($funding_source);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function deleted( ){
    try{
        $projects = Project::onlyTrashed()->get();

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
