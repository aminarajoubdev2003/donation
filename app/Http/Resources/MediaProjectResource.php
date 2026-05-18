<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaProjectResource extends JsonResource
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
            'cover_image' => !empty($this->cover_image) ? Storage::url($this->cover_image) : 'لا يوجد صورة بعد لهذا المشروع',
            'sector' => $this->sector,
            'on_the_other_hand' => $this->on_the_other_hand,
            'images' =>  ImageResource::collection(
                collect($this->images ?? [])
            ),
            'videos' => VideoResource::collection(
                collect($this->videos ?? [])
            ),
        ];
    }
}
