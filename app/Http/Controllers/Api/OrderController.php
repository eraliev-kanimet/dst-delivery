<?php

namespace App\Http\Controllers\Api;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function info()
    {
        $statuses = [];

        foreach (array_slice(OrderStatus::cases(), 1) as $status) {
            $statuses[] = [
                'key' => $status->value,
                'name' => __('common.order_status.' . $status->name)
            ];
        }

        $payment_methods = [];

        foreach (PaymentMethod::cases() as $type) {
            $payment_methods[] = [
                'key' => $type->value,
                'name' => __('common.payment_methods.' . $type->name)
            ];
        }

        $delivery_types = [];

        foreach (DeliveryType::cases() as $type) {
            $delivery_types[] = [
                'key' => $type->value,
                'name' => __('common.delivery_types.' . $type->name)
            ];
        }

        return response()->json([
            'statuses' => $statuses,
            'payment_methods' => $payment_methods,
            'delivery_types' => $delivery_types,
            'websocket' => [
                'url' => config('websocket.url'),
                'host' => config('websocket.host'),
                'port' => config('websocket.port'),
                'events' => [
                    'customer-{customer_id}',
                ],
                'query' => [
                    'customer_id' => 'customer_id',
                ]
            ],
        ]);
    }
}
