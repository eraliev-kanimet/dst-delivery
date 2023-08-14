<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {}

    public function store($request)
    {}

    public function show(Order $order)
    {}

    public function update($request, Order $order)
    {}

    public function cancel(Order $order)
    {}

    public function productAdd($request)
    {}

    public function productRemove($request)
    {}
}
