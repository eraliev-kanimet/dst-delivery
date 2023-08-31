<?php

namespace App\BaseModels;

use App\Models\AttrKey;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttrValueBase extends Model
{
    protected $casts = [
        'value' => 'array',
    ];

    public function attrKey(): BelongsTo
    {
        return $this->belongsTo(AttrKey::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public $timestamps = false;
}
