<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Services\Dashboard\DashboardChartService;
use App\Services\Dashboard\DashboardStatisticsService;
use App\Services\Dashboard\SmartInsightService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{   use GeneralTrait;

    public function __invoke( DashboardStatisticsService $statisticsService ,
    DashboardChartService $chartService, SmartInsightService $insightService)
    {
        return $this->apiResponse( [
        'statistics' => $statisticsService->getStatistics(),
        'charts' => [
            'monthly_donations' => $chartService->monthlyDonations(),
            'top_campaigns' => $chartService->topCampaigns(),
            'most_delayed_projects' => $chartService->mostDelayedProjects(),
            'donations_by_governorates' => $chartService->donationsByGovernorates(),
            'insights' => $insightService->insights()
            ]
        ] );
    }
}
