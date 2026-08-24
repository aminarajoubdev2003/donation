<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailProResource extends JsonResource
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
            'uuid' => $this->uuid,
            'detail' => $this->detail,
            'detail_cost' => $this->cost . ' $',
             'total_paid' => $this->pendings->sum('paid_amount') . ' $', 
             //'pendings' => PendingResource::collection( $this->whenLoaded('pendings') ),
             ];
    }
}
