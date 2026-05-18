<?php

namespace App\Http\Resources;

use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Inkind_donationResource extends JsonResource
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
            'governorate' => GovernorateResource::make(Governorate::findOrFail($this->governorate_id)),
            'name_of_material' => $this->name_of_material,
            'amount' => $this->amount,
            'type' => $this->type,
            'on_the_other_hand' => $this->on_the_other_hand,
            'status_of_materail' => $this->status_of_materail,
            'delivery_method' => $this->delivery_method,
            'status' => $this->status,
            'images' => collect($this->images ?? [])
            ->values()
            ->map(fn ($image, $index) => new ImageResource([
            'index' => $index,
            'path' => $image
            ]))
        ];
    }
}
