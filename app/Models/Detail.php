<?php

namespace App\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Detail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'project_id',
        'detail',
        'cost'
    ];

    public function project():BelongsTo{
        return $this->belongsTo(Project::class);
    }
}
