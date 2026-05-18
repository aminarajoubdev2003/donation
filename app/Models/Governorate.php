<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    protected $fillable = [
        'uuid',
        'governorate_name',

    ];

    public function cities(): HasMany{
        return $this->hasmany(City::class);
    }

    public function inkind_donations(): HasMany{
        return $this->hasmany(Inkind_donation::class);
    }
}
