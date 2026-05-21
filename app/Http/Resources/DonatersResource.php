<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonatersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       // return parent::toArray($request);
       $user_id = Matching::where('id',$this->video_able_id)->value('uuid');
       return[
        'user' => UserResource::make(User::findOrFail($this->user_id)),
        'last_donation' =>
        'date' =>
        'method' =>
       ];
    }
}
