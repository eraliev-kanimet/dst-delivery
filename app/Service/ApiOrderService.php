<?php

namespace App\Service;

use App\Enums\OrderStatus;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Selection;
use App\Models\Store;
use App\Service\Admin\NotificationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiOrderService
{
    public function __construct(
        public NotificationService $notificationService,
    )
    {}

    public function create(array $data): OrderResource
    {
        $store = Store::current();

        /** @var Customer $customer */
        $customer = Auth::user();

        $order = Order::create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'status' => OrderStatus::pending_payment->value,
            'delivery_date' => now()->addDays(10),
            'delivery_type' => $data['delivery_type'],
            'payment_type' => $data['payment_method'],
            'delivery_address' => $data['delivery_address'],
        ]);

        /** @var Selection $product */
        foreach ($data['products'] as $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product' => [
                    'selection_id' => $product->id
                ],
                'quantity' => $product->quantity,
                'price' => $product->price,
            ]);
        }

        $order->actionTotalCostRecalculation();

        $this->notificationService->send($store, __('notifications.orders.new1', [
            'order_id' => "#$order->uuid", 'customer' => $customer->phone,
        ]));

        $this->notificationService->sendToOwner($store, __('notifications.orders.new1', [
            'order_id' => "#$order->uuid", 'customer' => $customer->phone,
        ]));

        return new OrderResource($order);
    }

    public function getAll(bool|int $status = false, int $limit = 15): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with('orderItems')
            ->whereStoreId(Store::current()->id)
            ->whereCustomerId(Auth::user()->id);

        if ($status) {
            $orders->whereStatus($status);
        }

        return OrderResource::collection($orders->paginate($limit));
    }

    public function getByUuid(string $uuid): ?Order
    {
        $order = Order::whereUuid($uuid)->first();

        if ($order) {
            return $order;
        }

        throw new NotFoundHttpException(__('validation2.order.text4'));
    }

    public function getOrderItem(string $id): OrderItem
    {
        $item = OrderItem::find($id);

        if ($item) {
            return $item;
        }

        throw new NotFoundHttpException(__('validation2.order.text3'));
    }
}
