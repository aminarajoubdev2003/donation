<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiCampaignResource extends JsonResource
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
            'campaing' => CampaignResource::make(Campaign::findOrFail($this->id)),
            'projects_count' => $this->projects_count,
            'progresspercentage' => $this->progress_percentage.' '.'%'
        ];
    }
}
