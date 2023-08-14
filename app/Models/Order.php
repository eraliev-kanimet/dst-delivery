<?php

namespace App\Models;

use App\Traits\Models\OrderAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use OrderAction;

    protected $fillable = [
        'uuid',
        'store_id',
        'customer_id',
        'status',
        'total',
        'delivery_date',
        'delivery_address',
        'delivery_type',
        'payment_type',
    ];

    protected $casts = [
        'delivery_address' => 'array'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (self $order) {
            $order->uuid = time();
        });
    }
}