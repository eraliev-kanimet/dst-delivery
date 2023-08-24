<?php

namespace App\Http\Controllers\Api\Store;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\Order\ItemStoreRequest;
use App\Http\Requests\Api\Store\Order\ItemUpdateRequest;
use App\Http\Requests\Api\Store\Order\StoreRequest;
use App\Http\Requests\Api\Store\Order\UpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\OrderItem;
use App\Models\Selection;
use App\Models\Store;
use App\Service\ApiOrderService;
use App\Http\Requests\Api\Store\Order\IndexRequest;

class OrderController extends Controller
{
    public function __construct(
        protected ApiOrderService $service
    )
    {}

    public function index(IndexRequest $request)
    {
        return $this->service->getAll(
            $request->get('status', false),
            $request->get('limit', 15),
        );
    }

    public function store(StoreRequest $request)
    {
        return $this->service->create($request->all());
    }

    public function show(string $uuid)
    {
        return new OrderResource($this->service->getByUuid($uuid));
    }

    public function update(UpdateRequest $request, string $uuid)
    {
        $order = $this->service->getByUuid($uuid);

        if ($order->status == OrderStatus::pending_payment->value) {
            $order->update($request->all());

            $order->callCustomOrderUpdateEvent();

            return new OrderResource($order);
        }

        return response()->json(errors(__('validation2.the_order_cannot_be_changed')), 422);
    }

    public function cancel(string $uuid)
    {
        $order = $this->service->getByUuid($uuid);

        if ($order->status == OrderStatus::pending_payment->value) {
            $order->actionCancel();

            $order->callCustomOrderUpdateEvent();

            return new OrderResource($order);
        }

        return response()->json(errors(__('validation2.an_order_cannot_be_canceled')), 422);
    }

    public function itemAdd(ItemStoreRequest $request)
    {
        $order = $this->service->getByUuid($request->get('order_id'));

        if ($order->status == OrderStatus::pending_payment->value) {
            $selection = Selection::whereRelation('product', 'store_id', Store::current()->id)
                ->whereId($request->get('selection_id'))
                ->first();

            if ($selection) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product' => [
                        'selection_id' => $selection->id
                    ],
                    'quantity' => $request->get('quantity'),
                    'price' => $selection->price,
                ]);

                $order->actionTotalCostRecalculation();

                $order->callCustomOrderUpdateEvent();

                return new OrderResource($order);
            }

            return response()->json(errors(__('validation2.order.text2')), 404);
        }

        return response()->json(errors(__('validation2.the_order_cannot_be_changed')), 422);
    }

    public function itemUpdate(ItemUpdateRequest $request)
    {
        $orderItem = $this->service->getOrderItem($request->get('order_item_id'));

        $order = $orderItem->order;

        if ($order->status == OrderStatus::pending_payment->value) {
            $orderItem->update([
                'quantity' => $request->get('quantity'),
            ]);

            $order->actionTotalCostRecalculation();

            $order->callCustomOrderUpdateEvent();

            return new OrderResource($order);
        }

        return response()->json(errors(__('validation2.the_order_cannot_be_changed')), 422);
    }

    public function itemRemove(string $id)
    {
        $orderItem = $this->service->getOrderItem($id);

        $order = $orderItem->order;

        if ($order->orderItems()->count() == 1) {
            return response()->json(errors(__('validation2.order.text1')), 422);
        }

        if ($order->status == OrderStatus::pending_payment->value) {
            $orderItem->delete();

            $order->actionTotalCostRecalculation();

            $order->callCustomOrderUpdateEvent();

            return new OrderResource($order);
        }

        return response()->json(errors(__('validation2.the_order_cannot_be_changed')), 422);
    }
}
