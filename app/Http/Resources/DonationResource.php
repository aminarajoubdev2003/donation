<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'campaign' => CampaignResource::make(Campaign::findOrFail($this->campaign_id)),
            'contribution_amount' => $this->contribution_amount.' ' .'$',
            'contribution_details' => $this->contribution_details,
            'image' => new ImageResource([
            'index' => 0,
            'path' => $this->image
            ]),
            'status' => $this->status
        ];
    }
}
