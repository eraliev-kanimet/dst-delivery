<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttrValue extends Model
{
    protected $fillable = [
        'attr_key_id',
        'product_id',
        'value',
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
