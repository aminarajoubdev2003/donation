<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    protected $fillable = [
        'uuid',
        'district_name',
        'city_id',
    ];

    public function city():BelongsTo{
        return $this->belongsTo(City::class);
    }

    public function projects(): HasMany{
        return $this->hasmany(Project::class);
    }
}
