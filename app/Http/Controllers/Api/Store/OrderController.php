<?php

namespace App\Http\Controllers\Api\Store;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\Order\StoreRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Selection;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'withProduct' => ['nullable', 'in:0,1'],
            'limit' => ['nullable', 'numeric'],
        ]);

        $withProduct = $request->get('withProduct', 0);

        $orders = Order::with($withProduct ? 'orderItemsWithProduct' : 'orderItems')
            ->whereStoreId(Store::current()->id)
            ->whereCustomerId(Auth::user()->id)
            ->paginate($request->get('limit', 15));

        OrderResource::$withProduct = $withProduct;

        return OrderResource::collection($orders);
    }

    public function store(StoreRequest $request)
    {
        $order = Order::create([
            'store_id' => Store::current()->id,
            'customer_id' => Auth::user()->id,
            'status' => OrderStatus::pending_payment->value,
            'delivery_date' => now()->addDays(10),
            'delivery_type' => $request->get('delivery_type'),
            'payment_type' => $request->get('payment_type'),
            'delivery_address' => $request->get('delivery_address'),
        ]);

        /** @var Selection $product */
        foreach ($request->get('products') as $product) {
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

    public function show(string $uuid, Request $request)
    {
        $request->validate([
            'withProduct' => ['nullable', 'in:0,1'],
        ]);

        $store = Store::current();

        $order = Order::whereStoreId($store->id)->whereUuid($uuid)->whereCustomerId(Auth::user()->id)->first();

        if ($order) {
            OrderResource::$withProduct = $request->get('withProduct', 0);

            return new OrderResource($order);
        }

        return response()->json([], 404);
    }

    public function update($request, Order $order)
    {}

    public function cancel(Order $order)
    {}

    public function productAdd($request)
    {}

    public function productRemove($request)
    {}
}
