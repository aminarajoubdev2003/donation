<?php
namespace App\Services\Dashboard;
use App\Models\Detail;
use App\Models\Donation;
use App\Models\Project;
use App\Services\Dashboard\DashboardChartService;
use Carbon\Carbon;



class DashboardForecastService{

    public function projectRisks(): array
    {
    $projects = app(DashboardChartService::class)->mostDelayedProjects(20);

    return collect($projects)->map(function ($project) {
            $risk = 'Low';
            if ($project['delay_percentage'] >= 80) {
                $risk = 'High';
            } elseif ($project['delay_percentage'] >= 50) {
                $risk = 'Medium';
            }

            return [
                'project' => $project['project'],
                'risk' => $risk,
                'delay_percentage' => $project['delay_percentage']
            ];
        })->values()->toArray();
    }

    public function fundingGap(): float
    {
        return Detail::with('latestPending')->get()->sum(
            fn ($detail) => $detail->latestPending?->remaining_amount ?? 0
        );
    }

    public function priorityProjects(): array
    {
        return collect(app(DashboardChartService::class)->mostDelayedProjects(20))
        ->sortBy([
        ['delay_percentage', 'desc'],['remaining_amount', 'asc']])
        ->take(5)->values()->toArray();
    }

    public function donationForecast(): array
    {
        $avg = Donation::selectRaw('SUM(usd_amount) as total')
        ->where('created_at', '>=', now()->subMonths(3))
        ->first();

        $forecast = round($avg->total / 3,2);

        return [
            'next_month_forecast' => $forecast
        ];
    }

    public function timeDelayRisks(): array {

    $projects = Project::with(['campaigns','details.pendings'])->get();

    return $projects->map(function ($project) {

        $firstPending = $project->details->flatMap(function ($detail) {
                return $detail->pendings;
            })->sortBy('pending_date')->first();

        $campaign = $project->campaigns->first();

        if (!$firstPending || !$campaign) {
            return [
                'project' => $project->name,
                'risk' => 'Unknown',
                'gap_days' => null,
                'message' => 'لا توجد بيانات كافية'
            ];
        }

        $endDate = Carbon::parse($campaign->end_date);

        $paymentDate = Carbon::parse($firstPending->pending_date);

        $days = $endDate->diffInDays($paymentDate, false);

        $risk = 'Low';

        if ($days > 30) {
            $risk = 'High';
        } elseif ($days > 0) {
            $risk = 'Medium';
        }

        return [
            'project' => $project->name,
            'campaign_end' => $endDate->toDateString(),
            'first_payment' => $paymentDate->toDateString(),
            'gap_days' => $days,
            'risk' => $risk,
        ];

    })->values()->toArray();
    }
}
