<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'category',
        'on_the_other_hand',
        'excerpt',
        'content',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
