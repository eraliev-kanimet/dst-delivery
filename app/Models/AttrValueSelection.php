<?php

namespace App\Models;

use App\BaseModels\AttrValueBase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttrValueSelection extends AttrValueBase
{
    protected $fillable = [
        'attr_key_id',
        'product_id',
        'selection_id',
        'value',
    ];

    public function selection(): BelongsTo
    {
        return $this->belongsTo(Selection::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::saving(function (self $attrValue) {
            $attrValue->product_id = Selection::findOrFail($attrValue->selection_id)->product_id;
        });
    }
}
