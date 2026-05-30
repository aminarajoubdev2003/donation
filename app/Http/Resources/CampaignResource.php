<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
       // return parent::toArray($request);
       return [
        'uuid' => $this->uuid,
        'name' => $this->name,
        'target_amount' => $this->target_amount .' '.'$',
        'collected_amount' => $this->collected_amount,

        'start_date' => Carbon::parse($this->start_date)->format('d M Y'),
        'end_date'   => Carbon::parse($this->end_date)->format('d M Y'),

        'start_time' => Carbon::parse($this->start_time)->format('H:i:s'),
        'end_time'   => Carbon::parse($this->end_time)->format('H:i:s'),

        'purposes' => $this->purposes,
        'status' => $this->status,
        'collected_amount' => $this->collected_amount . ' $',
        'image' => Storage::url($this->image),
        'projects' => ProjectResource::collection($this->projects)
        ];
    }
}
