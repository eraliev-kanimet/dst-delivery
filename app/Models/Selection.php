<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Selection extends Model
{
    protected $fillable = [
        'product_id',
        'images',
        'properties',
        'quantity',
        'price',
        'is_available',
    ];

    protected $casts = [
        'images' => 'array',
        'properties' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
