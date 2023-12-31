<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'locales',
    ];

    protected $casts = [
        'locales' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function info(): HasOne
    {
        return $this->hasOne(StoreInfo::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function attr(): HasMany
    {
        return $this->hasMany(AttrKey::class);
    }

    public static function setCurrent(Store $store): void
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
