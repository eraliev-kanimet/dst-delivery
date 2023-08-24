<?php

namespace App\Events;

use Illuminate\Support\Facades\Http;

class CustomerOrder
{
    public static function dispatch(int $order_id, int $customer_id): void
    {
        Http::post(config('websocket.url'), [
            'command' => 'customer',
            'customer_id' => $customer_id,
            'message' => [
                'order_id' => $order_id,
                'current_date_time' => now(),
            ]
        ]);
    }
}
