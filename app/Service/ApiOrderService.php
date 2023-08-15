<?php

namespace App\Service;

use App\Enums\OrderStatus;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Selection;
use App\Models\Store;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ApiOrderService
{
    public function create(array $data): OrderResource
    {
        $order = Order::create([
            'store_id' => Store::current()->id,
            'customer_id' => Auth::user()->id,
            'status' => OrderStatus::pending_payment->value,
            'delivery_date' => now()->addDays(10),
            'delivery_type' => $data['delivery_type'],
            'payment_type' => $data['payment_type'],
            'delivery_address' => $data['delivery_address'],
        ]);

        /** @var Selection $product */
        foreach ($data['products'] as $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $product->quantity,
                'price' => $product->price,
            ]);
        }

        $order->actionTotalCostRecalculation();

        OrderResource::$withProduct = true;

        return new OrderResource($order);
    }

    public function getAll(bool $withProduct, bool|int $status = false, int $limit = 15): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with($withProduct ? 'orderItemsWithProduct' : 'orderItems')
            ->whereStoreId(Store::current()->id)
            ->whereCustomerId(Auth::user()->id);

        if ($status) {
            $orders->whereStatus($status);
        }

        OrderResource::$withProduct = $withProduct;

        return OrderResource::collection($orders->paginate($limit));
    }

    public function getByUuid(string $uuid): ?Order
    {
        return Order::whereStoreId(Store::current()->id)
            ->whereCustomerId(Auth::user()->id)
            ->whereUuid($uuid)
            ->first();
    }
}
