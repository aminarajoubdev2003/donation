<?php

namespace App\Models;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'target_amount',
        'SYP_amount',
        'USD_amount',
        'EUR_amount',
        'total',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'purposes',
        'status',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time'   => 'datetime:H:i:s',
        'target_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
    ];

    /**
     * Relations
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function projects(): BelongsToMany
    {
    return $this->belongsToMany(
        Project::class,
        'campaign_projects',
        'campaign_id',
        'project_id'
    );
    }

    public function donations(): HasMany{
        return $this->hasmany(Donation::class);
    }

    public function refreshStatus()
    {
    $now = now();

    if ($this->status === 'ملغاة') {
        return;
    }

    if ($this->status === 'متوقفة') {
        return;
    }

    if (
        $this->target_amount &&
        $this->collected_amount >= $this->target_amount
    ) {
        $this->update(['status' => 'مكتملة']);
        return;
    }

    $start = $this->start_date->copy()->setTimeFrom($this->start_time);
    $end   = $this->end_date->copy()->setTimeFrom($this->end_time);

    if ($now->greaterThan($end)) {
        $this->update(['status' => 'منتهية']);
    }

    elseif ($now->between($start,$end)) {
        $this->update(['status' => 'نشطة']);
    }

    /*elseif ($now->lessThan($start)) {
        $this->update(['status' => 'متوقفة']);
    }*/
    }


    public function getTotalAttribute(){

    $sypRate = ExchangeRate::where(
        'currency',
        'SYP'
    )->value('rate');

    $eurRate = ExchangeRate::where(
        'currency',
        'EUR'
    )->value('rate');

    $sypToUsd =
        $sypRate > 0
        ? $this->SYP_amount / $sypRate
        : 0;

    $eurToUsd =
        $eurRate > 0
        ? $this->EUR_amount / $eurRate
        : 0;

    return round(
        $this->USD_amount +
        $sypToUsd +
        $eurToUsd,
        2
    );
    }

}
