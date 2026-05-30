<?php
namespace App\Services\Dashboard;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Governorate;
use App\Models\Project;
use Carbon\Carbon;


class DashboardChartService{

    public function monthlyDonations(): array {
        $donations = Donation::query()->selectRaw("MONTH(created_at) as month,SUM(usd_amount) as total")
        ->whereYear('created_at',now()->year)
        ->groupByRaw('MONTH(created_at)')
        ->orderByRaw('MONTH(created_at)')->get();

        $months = [];

        foreach ($donations as $donation) {
            $months[] = [
            'month' => Carbon::create()->month($donation->month)->format('M'),
            'total' => round($donation->total, 2).' '.'$'
            ];
        }
        return $months;
    }

    public function topCampaigns(int $limit = 5): array {
    $campaigns = Campaign::query()
    ->withSum(['donations as total_donations'], 'usd_amount')
    ->orderByDesc('total_donations')->limit($limit)->get();

    return $campaigns->map(function ($campaign) {
        return [
            'campaign' => $campaign->name,
            'total' => round($campaign->total_donations ?? 0,2)
        ];
    })->toArray();
    }

    public function mostDelayedProjects(int $limit = 5): array {

    $projects = Project::with(['details.pendings'])->get();

    $data = [];

    foreach ($projects as $project) {
        $totalPaid = 0;
        foreach ($project->details as $detail) {
            $totalPaid += $detail->pendings->sum('paid_amount');
        }
        $remainingAmount = round($project->estimated_cost - $totalPaid,2);
        $delayPercentage = 0;

        if ($project->estimated_cost > 0) {
            $delayPercentage = round(($remainingAmount /$project->estimated_cost) * 100,2);
        }

        $data[] = [
            'project' => $project->name,
            'estimated_cost' => $project->estimated_cost,
            'total_paid' => round($totalPaid,2),
            'remaining_amount' => round($remainingAmount,2),
            'delay_percentage' => $delayPercentage
        ];
    }
    return collect($data)->sortByDesc('remaining_amount')->take($limit)->values()->toArray();
    }

    public function donationsByGovernorates(): array{
    $governorates = Governorate::with(['cities.districts.projects.campaigns.donations'])
    ->get();

    $data = [];

    foreach ($governorates as $governorate) {
        $total = 0;
        $processedCampaigns = [];

        foreach ($governorate->cities as $city) {
            foreach ($city->districts as $district) {
                foreach ($district->projects as $project) {
                    foreach ($project->campaigns as $campaign) {
                        if (in_array($campaign->id, $processedCampaigns)) {
                            continue;
                        }
                        $processedCampaigns[] = $campaign->id;
                        $total += $campaign->donations->sum('usd_amount');
                    }
                }
            }
        }

        $data[] = [
            'governorate' => $governorate->governorate_name,
            'total' => round( $total,2)
        ];
    }
    return collect($data)->sortByDesc('total')->values()->toArray();
    }
}
