<?php

namespace App\Http\Resources;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Models\Order;
use App\Models\Selection;
use Illuminate\Http\Request;

class OrderResource extends BaseResource
{
    public static bool $withProduct = false;

    /**
     * @var Order
     */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->uuid,
            'status' => $this->resource->status,
            'status_name' => __('common.order_status.' . OrderStatus::from($this->resource->status)->name),
            'total' => $this->resource->total,
            'delivery_date' => $this->resource->delivery_date,
            'delivery_address' => $this->resource->delivery_address,
            'delivery_type' => $this->resource->delivery_type,
            'delivery_type_name' => __('common.delivery_types.' . DeliveryType::from($this->resource->delivery_type)->name),
            'payment_type' => $this->resource->payment_type,
            'payment_type_name' => __('common.payment_types.' . PaymentType::from($this->resource->payment_type)->name),
            'items' => $this->getOrderItems(),
        ];
    }

    protected function getOrderItems(): array
    {
        $withProduct = self::$withProduct;

        $items = [];

        if ($withProduct) {
            $orderItems = $this->resource->orderItemsWithProduct;
        } else {
            $orderItems = $this->resource->orderItems;
        }

        foreach ($orderItems as $order) {
            $item = [
                'id' => $order->id,
                'selection_id' => $order->product_id,
                'quantity' => $order->quantity,
                'price' => $order->price,
            ];

            if ($withProduct) {
                $item['product'] = $this->product($order->product);
            }

            $items[] = $item;
        }

        return $items;
    }

    protected function product(Selection $product): array
    {
        $locale = self::$locale;

        return [
            'selection_id' => $product->id,
            'product_id' => $product->product_id,
            'name' => $product->product->{"content_$locale"}->name,
            'category' => [
                'id' => $product->product->category->id,
                'name' => $product->product->category->name[$locale],
            ],
            'images' => getImages(
                array_unique(
                    array_merge($product->images ?? [], $product->product->images->values ?? [])
                )
            ),
            'is_available' => $product->is_available && $product->product->is_available,
        ];
    }
}
