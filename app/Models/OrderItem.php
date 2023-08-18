<?php

namespace App\Models;

use App\Service\ProductSelectionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product',
        'quantity',
        'price',
    ];

    protected $casts = [
        'product' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();

        self::updating(function (self $item) {
            $item->updateProduct();
        });

        self::creating(function (self $item) {
            $item->updateProduct();
        });
    }

    protected function updateProduct(): void
    {
        $selection = Selection::find($this->product['selection_id']);

        if ($selection) {
            $this->product = ProductSelectionService::new()->creatingProductForOrder($selection);

            $this->price = $selection->price;
        }
    }
}
