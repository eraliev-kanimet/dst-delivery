<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'category_id',
        'sorted',
        'is_available',
        'preview',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function selections(): HasMany
    {
        return $this->hasMany(ProductSelection::class);
    }

    public function images(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }

    public function content_en(): HasOne
    {
        return $this->hasOne(ProductContent::class)->where('locale', 'en');
    }

    public function content_ru(): HasOne
    {
        return $this->hasOne(ProductContent::class)->where('locale', 'ru');
    }
}
