<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\HasApiTokens;

class Customer extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'phone',
        'active'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
