<?php

namespace App\Http\Resources;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
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
            'status_name' => $this->getStatusName(),
            'total' => $this->resource->total,
            'delivery_date' => $this->resource->delivery_date,
            'delivery_address' => $this->resource->delivery_address,
            'delivery_type' => $this->resource->delivery_type,
            'delivery_type_name' => $this->getDeliveryName(),
            'payment_method' => $this->resource->payment_type,
            'payment_method_name' => $this->getPaymentMethod(),
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
                    'name' => $this->getProductValue($product, 'name'),
                    'description' => $this->getProductValue($product, 'description'),
                    'category' => [
                        'id' => $product['category']['id'],
                        'name' => $this->getCategoryName($product['category']['name']),
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

    protected function getStatusName(): array|string
    {
        $type = OrderStatus::from($this->resource->status)->name;

        if (count(self::$locales)) {
            $array = [];

            foreach (self::$locales as $locale) {
                $array[$locale] = __('common.order_status.' . $type, locale: $locale);
            }

            return $array;
        }

        return __('common.order_status.' . $type);
    }

    protected function getDeliveryName(): array|string
    {
        $type = DeliveryType::from($this->resource->delivery_type)->name;

        if (count(self::$locales)) {
            $array = [];

            foreach (self::$locales as $locale) {
                $array[$locale] = __('common.delivery_types.' . $type, locale: $locale);
            }

            return $array;
        }

        return __('common.delivery_types.' . $type);
    }

    protected function getPaymentMethod(): array|string
    {
        $type = PaymentMethod::from($this->resource->payment_type)->name;

        if (count(self::$locales)) {
            $array = [];

            foreach (self::$locales as $locale) {
                $array[$locale] = __('common.payment_methods.' . $type, locale: $locale);
            }

            return $array;
        }

        return __('common.payment_methods.' . $type);
    }

    public function getProductValue(array $data, string $value): array|string
    {
        if (count(self::$locales)) {
            $array = [];

            foreach (self::$locales as $locale) {
                $array[$locale] = $data["content_$locale"][$value];
            }

            return $array;
        }

        return $data["content_" . self::$locale][$value];
    }

    public function getCategoryName(array $data): array|string
    {
        if (count(self::$locales)) {
            $array = [];

            foreach (self::$locales as $locale) {
                $array[$locale] = $data[$locale];
            }

            return $array;
        }

        return $data[self::$locale];
    }
}
