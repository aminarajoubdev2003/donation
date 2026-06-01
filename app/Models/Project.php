<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'district_id',
        'estimated_cost',
        'progress_percentage',
        'requirements',
        'cover_image',
        'sector',
        'on_the_other_hand',
        'images',
        'videos',
        'funding_source',
        'status',
        'Implementing_party',
    ];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
    ];

    public function district():BelongsTo{
        return $this->belongsTo(District::class);
    }

    public function campaigns(): BelongsToMany
    {
    return $this->belongsToMany(
        Campaign::class,
        'campaign_projects',
        'project_id',
        'campaign_id'
    )->wherePivotNull('deleted_at');
    }

    public function details(): HasMany{
        return $this->hasmany(Detail::class);
    }

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

    public function removeVideoByIndex($index)
    {
    $videos = $this->videos ?? [];

    if (isset($videos[$index])) {
        array_splice($videos, $index, 1);
        $this->videos = array_values($videos);
        $this->save();
    }

    return $this;
    }

}
