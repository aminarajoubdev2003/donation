<?php

namespace App\Http\Resources;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       // return parent::toArray($request);
       return[
        'uuid' => $this->uuid,
        'district_name' => $this->district_name,
        'city' => CityResource::make(City::findOrFail($this->city_id)),
       ];
    }
}
