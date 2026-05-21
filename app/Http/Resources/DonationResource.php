<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use App\Models\User;
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
            'user' => UserResource::make(User::findOrFail($this->user_id)),
            'contribution_amount' => $this->contribution_amount,
            'currency_type' => $this->currency_type,
            'contribution_details' => $this->contribution_details,
            'image' => new ImageResource([
            'index' => 0,
            'path' => $this->image
            ]),
            'status' => $this->status,
            'pending' => $this->pending
        ];
    }
}
