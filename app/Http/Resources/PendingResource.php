<?php

namespace App\Http\Resources;

use App\Models\Detail;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $detail = Detail::with('project')->find($this->detail_id);
        //return parent::toArray($request);
        return[
        'uuid' => $this->uuid,
        'project' => $detail && $detail->project ? [
            'uuid' => $detail->project->uuid,
            'name' => $detail->project->name,
        ] : null,
        'detail' => DetailResource::make(Detail::findOrFail($this->detail_id)),
        'pending_date' => Carbon::parse($this->pending_date)->format('d M Y'),
        'paid_amount' => $this->paid_amount.' '.'$',
        'cost' => $this->cost.' '.'$',
        'remaining_amount' => $this->remaining_amount.' '.'$'
       ];
    }
}
