<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
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
       return[
        'user' => UserResource::make(User::findOrFail($this->user_id)),
        'last_donation' => $this->contribution_amount,
        'date' => Carbon::parse($this->created_at)->format('d M Y'),
        'method' => ($this->donate_directly==1) ? 'تبرع' : 'تعهد ',
        'status' => ($this->pending==1) ? 'مدفوع' : 'غير مدفوع',
       ];
    }
}
