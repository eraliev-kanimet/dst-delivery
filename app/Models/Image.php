<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = [
        'values',
        'imageable_id',
        'imageable_type',
    ];

    protected $casts = [
        'values' => 'array'
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    public $timestamps = false;
}
