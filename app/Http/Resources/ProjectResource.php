<?php

namespace App\Http\Resources;

use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'district' => DistrictResource::make(District::findOrFail($this->district_id)),
            'estimated_cost' => $this->estimated_cost.' ' .'$',
            'progress_percentage' => ($this->progress_percentage ?? 0) . ' %',
            'requirements' => $this->requirements,
            'cover_image' => !empty($this->cover_image) ? Storage::url($this->cover_image) : 'لا يوجد صورة بعد لهذا المشروع',
            'sector' => $this->sector,
            'on_the_other_hand' => $this->on_the_other_hand,
            'images' => collect($this->images ?? [])
            ->values()
            ->map(fn ($image, $index) => new ImageResource([
            'index' => $index,
            'path' => $image
            ])),
            'videos' => collect($this->videos ?? [])
            ->values()
            ->map(fn ($video, $index) => new ImageResource([
            'index' => $index,
            'path' => $video
            ])),
            'funding_source' => $this->funding_source,
            'Implementing_party' => $this->Implementing_party,
            'status' => $this->status,
            'details' => DetailResource::collection($this->details),
        ];
    }
}
