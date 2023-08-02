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

    public function images(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getImages(): array
    {
        $images = [];

        foreach ($this->images->values ?? [] as $image) {
            $images[] = asset('storage/' . $image);
        }

        return $images;
    }

    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();

        self::deleted(function (self $category) {
            if ($category->category) {
                $category->category->updateChildren();
            }
        });

        self::saved(function (self $category) {
            if ($category->category) {
                $category->category->updateChildren();
            }
        });
    }

    public function updateChildren(): void
    {
        $this->update([
            'children' => $this->getCategoriesIds(),
        ]);
    }

    protected function getCategoriesIds(): array
    {
        $ids = [$this->id];

        foreach (Category::whereCategoryId($this->id)->get(['id', 'category_id']) as $category) {
            $ids = array_merge($ids, $category->getCategoriesIds());
        }

        return $ids;
    }
}
