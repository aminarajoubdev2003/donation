<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Services\Dashboard\DashboardChartService;
use App\Services\Dashboard\DashboardStatisticsService;
use App\Services\Dashboard\SmartInsightAIService;
use App\Services\Dashboard\SmartInsightService;
use App\Services\Dashboard\DashboardForecastService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{   use GeneralTrait;

    public function __invoke( DashboardStatisticsService $statisticsService ,
    DashboardChartService $chartService, SmartInsightService $insightService,
    SmartInsightAIService $aiService, DashboardForecastService $forecastService)
    {
        /*return $this->apiResponse( [
        'statistics' => $statisticsService->getStatistics(),
        'charts' => [
            'monthly_donations' => $chartService->monthlyDonations(),
            'top_campaigns' => $chartService->topCampaigns(),
            'most_delayed_projects' => $chartService->mostDelayedProjects(),
            'donations_by_governorates' => $chartService->donationsByGovernorates(),
            //'insights' => $insightService->insights()
            ]
        ] );*/
        $statistics = $statisticsService->getStatistics();

        $charts = [
        'monthly_donations' => $chartService->monthlyDonations(),
        'top_campaigns' => $chartService->topCampaigns(),
        'most_delayed_projects' => $chartService->mostDelayedProjects(),
        'donations_by_governorates' => $chartService->donationsByGovernorates()
        ];

        $forecasts = [
        'project_risks' => $forecastService->projectRisks(),
        'time_delay_risks' => $forecastService->timeDelayRisks(),
        'funding_gap' => $forecastService->fundingGap(),
        'priority_projects' => $forecastService->priorityProjects(),
        'donation_forecast' => $forecastService->donationForecast(),
        ];

        $data = array_merge($statistics,$charts);
        $aiInsights = $aiService->generateInsights($data);

        return $this->apiResponse([
            'statistics' => $statistics,
            'charts' => $charts,
            'ai_insights' => $aiInsights,
            'forecasts' => $forecasts,
        ]);
    }
}
