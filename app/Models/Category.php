<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Category extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'children',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'children' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(self::class);
    }

    public function _images(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();

        self::created(function (self $category) {
            $category->_images()->save(new Image);
        });
    }
}
