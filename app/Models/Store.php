<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Store extends Model
{
    private static self $current;

    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'fallback_locale',
        'locales'
    ];

    protected $casts = [
        'locales' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function info(): HasOne
    {
        return $this->hasOne(StoreInfo::class);
    }

    public static function setCurrent($store): void
    {
        self::$current = $store;
    }

    public static function current(): Store
    {
        return self::$current;
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (self $store) {
            $store->uuid = Str::uuid();
        });

        self::created(function (self $store) {
            $store->info()->save(new StoreInfo);
        });
    }
}
