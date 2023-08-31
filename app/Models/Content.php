<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = [
        'product_id',
        'locale',
        'name',
        'description',
    ];

    public $timestamps = false;
}
