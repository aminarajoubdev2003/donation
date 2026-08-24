<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingProResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [ 'uuid' => $this->uuid,
        'pending_date' => $this->pending_date ? Carbon::parse($this->pending_date)->format('d M Y') : null,
        'paid_amount' => $this->paid_amount . ' $',
        'cost' => $this->cost . ' $',
        'remaining_amount' => $this->remaining_amount . ' $',
        ];
    }
}
