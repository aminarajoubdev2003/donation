<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'category',
        'on_the_other_hand',
        'excerpt',
        'content',
        'images',
    ];
}
