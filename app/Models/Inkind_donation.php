<?php

namespace App\Models;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inkind_donation extends Model
{
    protected $fillable = [
        'uuid',
        'governorate_id',
        'name_of_material',
        'amount',
        'type',
        'on_the_other_hand',
        'images',
        'status_of_materail',
        'delivery_method',
        'status'
    ];

    public function governorate():BelongsTo{
        return $this->belongsTo(Governorate::class);
    }
}
