<?php

namespace App\Services\Dashboard;

use App\Models\Detail;
use App\Models\Donation;
use App\Models\Pending;
use App\Models\Project;
use App\Models\User;


class DashboardStatisticsService{

    public function getStatistics() {
        $totalDonations = Donation::sum('usd_amount');
        $totalDonors = User::count();
        $totalProjects = Project::count();
        $completedProjects = $this->calculateCompletedProjects();
        $uncompletedProjects = $this->countUncompletedProjects();
        $active_details_remaining_amount = $this->calculateOutstandingAmounts();
        $funding_progress_rate = $this->calculateCompletionRate();

        return [
            'total_donations' => round($totalDonations, 2),
            'total_donors' => $totalDonors,
            'total_projects' => $totalProjects,
            'completed_projects' => $completedProjects,
            'uncompleted_projects' => $uncompletedProjects,
            'active_details_remaining_amount' => round($active_details_remaining_amount, 2),
            'funding_progress_rate' => $funding_progress_rate .' '.'%',
        ];
    }

    private function countUncompletedProjects(): int
    {
    $projects = Project::with('details.latestPending')->get();

    return $projects->filter(function ($project) {

        if ($project->details->isEmpty()) {
            return true;
        }

        foreach ($project->details as $detail) {
            if (!$detail->latestPending ||
                $detail->latestPending->remaining_amount > 0) {
                return true;
            }
        }

        return false;
    })->count();
    }

    private function calculateOutstandingAmounts(): float {
        $details = Detail::with('latestPending')->get();

        $totalRemaining = $details->sum(fn($detail) =>
        $detail->latestPending?->remaining_amount ?? 0);

        return round( $totalRemaining, 2);
    }

    private function calculateCompletedProjects(): int
    {
        $projects = Project::with(['details.pendings'])->get();
        $completedProjects = 0;

        foreach ($projects as $project) {
        $isCompleted = $project->details->isNotEmpty();;

        foreach ($project->details as $detail) {
            $totalPaid = $detail->pendings->sum('paid_amount');
            if ($totalPaid < $detail->cost) {
                $isCompleted = false;
                break;
            }
        }
        if ($isCompleted) {
            $completedProjects++;
        }
    }
    return $completedProjects;
    }

    private function calculateCompletionRate(): float
    {
        $totalCost = Detail::sum('cost');

        $totalPaid = Pending::sum('paid_amount');

        if ($totalCost <= 0) {
            return 0;
        }

        return round(($totalPaid / $totalCost) * 100, 2);
    }
}
