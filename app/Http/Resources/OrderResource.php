<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderResource extends BaseResource
{
    /**
     * @var Order
     */
    public $resource;

    public function toArray(Request $request): array
    {
        return [

        ];
    }
}
