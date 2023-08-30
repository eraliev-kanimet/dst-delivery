<?php

namespace App\Models;

use App\BaseModels\AttrValueBase;

class AttrValue extends AttrValueBase
{
    protected $fillable = [
        'attr_key_id',
        'product_id',
        'value',
    ];
}
