<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
        'name' => $this->name,
        'email' => $this->email,
        'phone' => $this->phone,
        'type' => $this->type,
        'profile' => !empty($this->profile) ? Storage::url($this->profile) : null
       ];
    }
}
