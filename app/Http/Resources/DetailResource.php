<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return[
        'uuid' => $this->uuid,
        //'project' => ProjectResource::make(Project::findOrFail($this->project_id)),
        'detail' => $this->detail,
        'cost' => $this->cost.''.'$'
       ];
    }
}
