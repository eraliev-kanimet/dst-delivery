<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'image',
        'type',
        'type_value',
        'start_date',
        'end_date',
        'sorted',
        'active',
    ];

    protected $casts = [
        'image' => 'array'
    ];

    public static array $types = [
        'url' => 'URL',
        'product' => 'Product',
        'category' => 'Category',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
