<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCampaignNotification extends Notification
{
    use Queueable;

    private $campaign;

    public function __construct($campaign)
    {
        $this->campaign = $campaign;
    }

    public function via($notifiable): array
    {
        return ['database']; // 🔥 هنا المهم
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => '📢 حملة جديدة',
            'message' => 'تم إطلاق حملة: ' . $this->campaign->name,
            'campaign_uuid' => $this->campaign->uuid,
        ];
    }
}
