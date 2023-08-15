<?php

namespace App\Http\Controllers\Api;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
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

        $payment_types = [];

        foreach (PaymentType::cases() as $type) {
            $payment_types[] = [
                'key' => $type->value,
                'name' => __('common.payment_types.' . $type->name)
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
            'payment_types' => $payment_types,
            'delivery_types' => $delivery_types,
        ]);
    }
}
