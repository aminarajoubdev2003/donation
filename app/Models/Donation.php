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
        'image',
        'pending'
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
