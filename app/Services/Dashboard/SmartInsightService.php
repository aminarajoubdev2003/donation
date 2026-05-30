<?php

namespace App\Services\Dashboard;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Governorate;
use App\Models\Project;

class SmartInsightService
{
    public function insights(): array
    {
        $insights = [];
        /*
        Monthly Donation Growth
        */
        $growth = $this->monthlyDonationGrowth();
        if ($growth > 0) {
            $insights[] = [
                'type' => 'growth',
                'message' => "ارتفعت التبرعات بنسبة {$growth}% مقارنة بالشهر الماضي."
            ];

        } elseif ($growth < 0) {
            $insights[] = [
                'type' => 'warning',
                'message' => "انخفضت التبرعات بنسبة ". abs($growth). "% مقارنة بالشهر الماضي."
            ];
        }

        /*
        High Risk Projects
        */

        $highRiskProjects = $this->highRiskProjects();

        if ($highRiskProjects > 0) {
            $insights[] = [
                'type' => 'risk',
                'message' => "يوجد {$highRiskProjects} مشاريع عالية التعثر تحتاج دعماً عاجلاً."
            ];
        }

        /*
       Top Campaign
        */

        $topCampaign = $this->topCampaign();
        if ($topCampaign) {
            $insights[] = [
                'type' => 'success',
                'message' => "الحملة الأعلى تمويلاً حالياً هي {$topCampaign}."
            ];
        }

        /*
       Top Governorate
        */

        $topGovernorate = $this->topGovernorate();
        if ($topGovernorate) {
            $insights[] = [
                'type' => 'location',
                'message' => "المحافظة الأعلى مساهمة بالتبرعات هي {$topGovernorate}."
            ];
        }
        return $insights;
    }

    /**
     * مقارنة هذا الشهر بالشهر الماضي
     */
    private function monthlyDonationGrowth(): float
    {
        $currentMonth = Donation::whereMonth('created_at',now()->month)->sum('usd_amount');

        $previousMonth = Donation::whereMonth('created_at',now()->subMonth()->month)->sum('usd_amount');

        if ($previousMonth <= 0) {
            return 0;
        }
        return round((($currentMonth - $previousMonth)/ $previousMonth) * 100,2);
    }

    /**
     * المشاريع عالية الخطورة
     */
    private function highRiskProjects(): int
    {
        $projects = Project::with(['details.latestPending'])->get();

        $count = 0;
        foreach ($projects as $project) {
            $remaining = 0;
            foreach ($project->details as $detail) {
                $remaining += $detail->latestPending?->remaining_amount ?? 0;
            }
            if ($project->estimated_cost <= 0) {
                continue;
            }

            $delayPercentage = ($remaining /$project->estimated_cost) * 100;
            if ($delayPercentage >= 70) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * أعلى حملة تمويلاً
     */
    private function topCampaign(): ?string
    {
        $campaign = Campaign::query()
        ->withSum('donations','usd_amount')->orderByDesc('donations_sum_usd_amount')
        ->first();
        return $campaign?->name;
    }

    /**
     * المحافظة الأعلى بالتبرعات
     */
    private function topGovernorate(): ?string
    {
        $governorates = Governorate::with(['cities.districts.projects.campaigns.donations'])->get();

        $topGovernorate = null;
        $topAmount = 0;

        foreach ($governorates as $governorate) {
            $total = 0;
            $processedCampaigns = [];

            foreach ($governorate->cities as $city) {
                foreach ($city->districts as $district) {
                    foreach ($district->projects as $project) {
                        foreach ($project->campaigns as $campaign) {
                            if (in_array($campaign->id,$processedCampaigns)) {
                                continue;
                            }
                            $processedCampaigns[] = $campaign->id;
                            $total += $campaign->donations->sum('usd_amount');
                        }
                    }
                }
            }

            if ($total > $topAmount) {
                $topAmount = $total;
                $topGovernorate = $governorate->governorate_name;
            }
        }
        return $topGovernorate;
    }
}
