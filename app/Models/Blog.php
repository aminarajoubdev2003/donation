<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
        'cover_image'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function removeImageByIndex($index)
    {
    if (!$this->images || !isset($this->images[$index])) {
        return $this;
    }

    $imagePath = $this->images[$index];

    // حذف من التخزين
    if (Storage::disk('public')->exists($imagePath)) {
        Storage::disk('public')->delete($imagePath);
    }

    // حذف من المصفوفة
    $images = $this->images;
    array_splice($images, $index, 1);
    $this->images = array_values($images);
    $this->save();

    return $this;
    }
}
