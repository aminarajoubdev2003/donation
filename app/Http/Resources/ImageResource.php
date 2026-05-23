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
        $path = $this->resource['path'];

        return [
            'index' => $this->resource['index'],

            'url' => str_starts_with($path, 'http')
                ? $path
                : Storage::url($path),
        ];
    }
}
