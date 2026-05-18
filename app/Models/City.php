<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $fillable = [
        'uuid',
        'city_name',
        'governorate_id',
    ];

    public function governorate():BelongsTo{
        return $this->belongsTo(Governorate::class);
    }

    public function districts(): HasMany{
        return $this->hasmany(District::class);
    }
}
