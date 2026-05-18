<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign_project extends Model
{
    use SoftDeletes ;

    protected $fillable = [
        'uuid',
        'project_id',
        'campaign_id',
    ];
}
