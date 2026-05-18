<?php

namespace App\Observers;

use App\Models\Campaign;
use App\Models\User;
use App\Notifications\NewCampaignNotification;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewCampaignMail;



class CampaignObserver
{
    /**
     * Handle the Campaign "created" event.
     */

    public function created(Campaign $campaign): void
    {
        User::where('type', '!=', 'أدمن')
        ->chunk(10, function ($users) use ($campaign) {
        foreach ($users as $index => $user) {
            Mail::to($user->email)
                ->later(now()->addSeconds($index * 10), new NewCampaignMail($campaign));
        }
    });
    /*\DB::afterCommit(function () use ($campaign) {

        $firebase = new FirebaseService();

        $users = User::where('type', '!=', 'أدمن')->get();

        foreach ($users as $user) {

            // 1️⃣ Database Notification
            $user->notify(new NewCampaignNotification($campaign));

            // 2️⃣ Firebase Notification (إذا عنده token)
            if ($user->fcm_token) {
                try {
                    $firebase->sendNotification(
                        $user->fcm_token,
                        'حملة جديدة 🎉',
                        "تم إنشاء حملة: {$campaign->name}"
                    );
                } catch (\Exception $e) {
                    \Log::error("FCM Error: " . $e->getMessage());
                }
            }
        }

    });*/
    }

    /**
     * Handle the Campaign "updated" event.
     */
    public function updated(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Campaign "deleted" event.
     */
    public function deleted(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Campaign "restored" event.
     */
    public function restored(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Campaign "force deleted" event.
     */
    public function forceDeleted(Campaign $campaign): void
    {
        //
    }
}
