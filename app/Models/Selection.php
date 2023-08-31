<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Selection extends Model
{
    protected $fillable = [
        'product_id',
        'images',
        'quantity',
        'price',
        'is_available',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attr(): HasMany
    {
        return $this->hasMany(AttrValueSelection::class);
    }
}
