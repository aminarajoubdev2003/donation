<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Donater_ShowResource extends JsonResource
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
        'last_donation' => $this->contribution_amount,
        'currency_type' => $this->currency_type,
        'campaing' => CampaignResource::make(Campaign::findOrFail($this->campaign_id)),
        'date' => Carbon::parse($this->created_at)->format('d M Y'),
        'method' => ($this->donate_directly==1) ? 'تبرع' : 'تعهد ',
        'status' => $this->status,
        'pending' => ($this->pending==1) ? 'مدفوع' : 'غير مدفوع',
       ];
    }
}
