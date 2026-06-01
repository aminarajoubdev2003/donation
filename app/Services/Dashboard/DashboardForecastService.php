<?php
namespace App\Services\Dashboard;
use App\Models\Detail;
use App\Models\Donation;
use App\Services\Dashboard\DashboardChartService;



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
}
