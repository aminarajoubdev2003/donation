<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class campaign_project extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [
            'project' => ProjectResource::make(Project::findOrFail($this->id)),
            'campaigns' => $this->campaigns->map(function ($campaign) {
            return [
            'uuid' => $campaign->uuid,
            'name' => $campaign->name,
            'status' => $campaign->status,
            ];
            }),
        ];
    }
}
