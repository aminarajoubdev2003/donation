<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatisticResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [
            'total_donations' => round($this->totalDonations, 2),
            'total_donors' => $this->totalDonors,
            'total_projects' => $this->totalProjects,
            'completed_projects' => $this->completedProjects,
            'pending_projects' => $this->pendingProjects,
            'total_pending_amounts' => round($this->totalPendingAmounts, 2),
            'completion_rate' => $this->completionRate,
        ];
    }
}
