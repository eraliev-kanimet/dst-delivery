<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class Customer extends Model
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
