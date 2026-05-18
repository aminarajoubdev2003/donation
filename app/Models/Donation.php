<?php

namespace App\Models;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'campaign_id',
        'contribution_amount',
        'contribution_details',
        'pledge_to_donate',
        'donate_directly',
        'status',
        'image'
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /*protected $appends = [
        'total_of_donations'
    ];

    public function get total_of_donations Attribuit(){
        $total = 0;

    }*/
}
