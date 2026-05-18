<?php

namespace App\Http\Resources;

use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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
        'city_name' => $this->city_name,
        'governorate' => GovernorateResource::make(Governorate::findOrFail($this->governorate_id)),
       ];
    }
}
