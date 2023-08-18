<?php

namespace App\Http\Resources;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Models\Order;
use App\Service\ProductSelectionService;
use Illuminate\Http\Request;

class OrderResource extends BaseResource
{
    /**
     * @var Order
     */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->uuid,
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
        $locale = self::$locale;
        $service = ProductSelectionService::new();

        $items = [];

        foreach ($this->resource->orderItems as $order) {
            $product = $order->product;

            $item = [
                'id' => $order->id,
                'product' => [
                    'product_id' => $product['product_id'],
                    'selection_id' => $product['selection_id'],
                    'name' => $product["content_$locale"]['name'],
                    'description' => $product["content_$locale"]['description'],
                    'category' => [
                        'id' => $product['category']['id'],
                        'name' => $product['category']['name'][$locale],
                    ],
                    'images' => getImages($product['images']),
                    'attributes' => $service->getAttributes($product['attributes'], $locale)
                ],
                'quantity' => $order->quantity,
                'price' => $order->price,
            ];

            $items[] = $item;
        }

        return $items;
    }
}
