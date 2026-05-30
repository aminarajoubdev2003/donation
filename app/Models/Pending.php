<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pending extends Model
{
    protected $fillable = [
    'uuid',
    'detail_id',
    'cost',
    'paid_amount',
    'remaining_amount',
    'pending_date',
    ];

    protected $appends = [
        'status',
        'completion_percentage',
    ];

    public function detail()
    {
    return $this->belongsTo(Detail::class);
    }

    public function getStatusAttribute()
    {
    return $this->remaining_amount <= 0 ? 'مكتمل': 'غير مكتمل';
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->cost <= 0) {
            return 0;
        }

        return round(($this->paid_amount / $this->cost) * 100, 2);
    }
}
