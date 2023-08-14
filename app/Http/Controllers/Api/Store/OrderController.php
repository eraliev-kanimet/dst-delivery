<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {}

    public function store($request)
    {}

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
