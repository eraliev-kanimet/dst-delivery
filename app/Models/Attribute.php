<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'attribute',
        'value1',
        'value2',
    ];

    protected $casts = [
        'value1' => 'array',
    ];

    public $timestamps = false;
}
