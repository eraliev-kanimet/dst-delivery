<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'store_id',
        'name',
        'phone',
        'active'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (self $customer) {
            $customer->name = Str::uuid();
        });
    }
}
