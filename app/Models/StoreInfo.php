<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreInfo extends Model
{
    protected $fillable = [
        'store_id',
        'images',
        'description',
        'contact',
    ];

    protected $casts = [
        'images' => 'array',
        'description' => 'array',
        'contact' => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public $timestamps = false;
}
