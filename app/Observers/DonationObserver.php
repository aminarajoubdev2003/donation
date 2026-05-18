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
        /*if (
            $donation->isDirty('status') &&
            $donation->status === 'متوافق'
        ) {

            $campaign = $donation->campaign;

            if ($campaign) {

                $campaign->increment(
                    'collected_amount',
                    $donation->contribution_amount
                );
            }
        }*/
        if (
            $donation->isDirty('status') &&
            $donation->status === 'متوافق'
        ) {

            $campaign = $donation->campaign;

            if ($campaign) {
                if( $donation['currency_type'] == 'SYP'){
                    $campaign->increment(
                    'SYP_amount',
                    $donation->contribution_amount);
                }
                elseif( $donation['currency_type'] == 'USD'){
                    $campaign->increment(
                    'USD_amount',
                    $donation->contribution_amount);
                }
                else{
                    $campaign->increment(
                    'EUR_amount',
                    $donation->contribution_amount);
                }
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
