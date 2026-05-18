<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Observers\CampaignObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\Donation;
use App\Observers\DonationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Campaign::observe(CampaignObserver::class);
        Donation::observe(DonationObserver::class);

    }
}
