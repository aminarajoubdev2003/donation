<?php

namespace App\Models;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'campaign_id',
        'contribution_amount',
        'contribution_details',
        'currency_type',
        'usd_amount',
        'pledge_to_donate',
        'donate_directly',
        'status',
        'file',
        'pending',
        'reason',
        'remaining_amount',
        'on_the_other_hand'
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
    return $this->belongsTo(User::class);
    }

}
