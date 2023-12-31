<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttrKey extends Model
{
    protected $fillable = [
        'store_id',
        'slug',
        'name',
        'translatable',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function productValues(): HasMany
    {
        return $this->hasMany(AttrValue::class);
    }

    public function selectionValues(): HasMany
    {
        return $this->hasMany(AttrValueSelection::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (self $key) {
            if (is_null($key->slug)) {
                $key->slug = time();
            }
        });
    }

    public $timestamps = false;
}
