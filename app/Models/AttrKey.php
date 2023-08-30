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

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttrValue::class);
    }

    public $timestamps = false;
}
