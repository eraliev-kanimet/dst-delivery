<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSelection extends Model
{
    protected $fillable = [
        'product_id',
        'attributes',
        'quantity',
        'price',
        'is_available',
    ];

    protected $casts = [
        'attributes' => 'array'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
