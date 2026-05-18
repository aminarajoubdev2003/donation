<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
            'index' => $this->resource['index'],
            'path'  => $this->resource['path'],
            'url'   => $this->resource['path']
                ? Storage::disk('public')->url($this->resource['path'])
                : null,
        ];
    }
}
