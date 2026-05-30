<?php

namespace App\Observers;

use App\Models\Donation;

class DonationObserver
{
    /**
     * Handle the Donation "created" event.
     */
    public function created(Donation $donation): void
    {
        //
    }

    /**
     * Handle the Donation "updated" event.
     */
    public function updated(Donation $donation): void
    {

        if (
            $donation->wasChanged('status') &&
            $donation->status === 'متوافق'
        ) {

            $campaign = $donation->campaign;

            if ($campaign) {

            $campaign->increment('collected_amount',$donation->usd_amount);
            }
        }
    }


    /**
     * Handle the Donation "deleted" event.
     */
    public function deleted(Donation $donation): void
    {
        //
    }

    /**
     * Handle the Donation "restored" event.
     */
    public function restored(Donation $donation): void
    {
        //
    }

    /**
     * Handle the Donation "force deleted" event.
     */
    public function forceDeleted(Donation $donation): void
    {
        //
    }
}
