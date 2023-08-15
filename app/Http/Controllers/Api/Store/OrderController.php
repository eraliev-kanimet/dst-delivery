<?php

namespace App\Http\Controllers\Api\Store;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\Order\ItemStoreRequest;
use App\Http\Requests\Api\Store\Order\StoreRequest;
use App\Http\Requests\Api\Store\Order\UpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\OrderItem;
use App\Models\Selection;
use App\Models\Store;
use App\Service\ApiOrderService;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Store\Order\IndexRequest;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        protected ApiOrderService $service
    )
    {}

    public function index(IndexRequest $request)
    {
        return $this->service->getAll(
            $request->get('withProduct', 0),
            $request->get('status', false),
            $request->get('limit', 15),
        );
    }

    public function store(StoreRequest $request)
    {
        return $this->service->create($request->all());
    }

    public function show(string $uuid, Request $request)
    {
        $request->validate([
            'withProduct' => ['nullable', 'in:0,1'],
        ]);

        $order = $this->service->getByUuid($uuid);

        if ($order) {
            OrderResource::$withProduct = $request->get('withProduct', 0);

            return new OrderResource($order);
        }

        return response()->json([], 404);
    }

    public function update(UpdateRequest $request, string $uuid)
    {
        $order = $this->service->getByUuid($uuid);

        if ($order) {
            if ($order->status == OrderStatus::pending_payment->value) {
                $order->update($request->all());

                return new OrderResource($order);
            }

            return response()->json(errors(__('validation2.the_order_cannot_be_changed')), 422);
        }

        return response()->json([], 404);
    }

    public function cancel(string $uuid)
    {
        $order = $this->service->getByUuid($uuid);

        if ($order) {
            if ($order->status == OrderStatus::pending_payment->value) {
                $order->actionCancel();

                return new OrderResource($order);
            }

            return response()->json(errors(__('validation2.an_order_cannot_be_canceled')), 422);
        }

        return response()->json([], 404);
    }

    public function itemAdd(ItemStoreRequest $request)
    {
        $order = $this->service->getByUuid($request->get('order_id'));

        if ($order) {
            if ($order->status == OrderStatus::pending_payment->value) {
                $selection = Selection::whereRelation('product', 'store_id', Store::current()->id)
                    ->whereId($request->get('selection_id'))
                    ->first();

                if ($selection) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $selection->id,
                        'quantity' => $request->get('quantity'),
                        'price' => $selection->price,
                    ]);

                    $order->actionTotalCostRecalculation();

                    return new OrderResource($order);
                }

                return response()->json(errors(__('validation2.order_api.text2')), 404);
            }

            return response()->json(errors(__('validation2.the_order_cannot_be_changed')), 422);
        }

        return response()->json([], 404);
    }

    public function itemRemove(string $id)
    {
        $orderItem = OrderItem::whereRelation('order', 'customer_id', Auth::user()->id)
            ->whereId($id)
            ->first();

        if ($orderItem) {
            $order = $orderItem->order;

            if ($order->orderItems()->count() == 1) {
                return response()->json(errors(__('validation2.order_api.text1')), 422);
            }

            if ($order->status == OrderStatus::pending_payment->value) {
                $orderItem->delete();

                $order->actionTotalCostRecalculation();

                return new OrderResource($order);
            }

            return response()->json(errors(__('validation2.the_order_cannot_be_changed')), 422);
        }

        return response()->json([], 404);
    }
}
