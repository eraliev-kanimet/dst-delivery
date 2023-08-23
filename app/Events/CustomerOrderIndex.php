<?php

namespace App\Events;

use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Queue\SerializesModels;

class CustomerOrderIndex implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AnonymousResourceCollection $data;

    public function __construct(protected Store $store, protected Customer $customer)
    {
        OrderResource::$locales = $store->locales;

        $this->data = OrderResource::collection(Order::whereCustomerId($customer->id)->get());
    }

    public function broadcastOn(): Channel
    {
        return new Channel('customer.' . $this->customer->id . '.orders');
    }

    public function broadcastAs(): string
    {
        return 'customer.orders';
    }
}
