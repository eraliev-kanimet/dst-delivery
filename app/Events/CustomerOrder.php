<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerOrder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public string $current_date_time;

    public function __construct(public int $order_id, protected int $customer_id)
    {
        $this->current_date_time = now();
    }

    public function broadcastOn(): Channel
    {
        return new Channel('customer.' . $this->customer_id . '.orders');
    }

    public function broadcastAs(): string
    {
        return 'customer.orders';
    }
}
