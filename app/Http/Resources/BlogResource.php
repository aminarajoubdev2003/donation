<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
        'publish_date' => Carbon::parse($this->created_at)->format('d M Y'),
        'title' => $this->title,
        'category' => $this->category,
        'on_the_other_hand' => $this->on_the_other_hand,
        'excerpt' => $this->excerpt,
        'content' => $this->content,
        'images' => collect($this->images ?? [])
            ->values()
            ->map(fn ($image, $index) => new ImageResource([
            'index' => $index,
            'path' => $image
            ])),
        ];
    }
}
