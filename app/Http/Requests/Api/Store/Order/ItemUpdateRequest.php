<?php

namespace App\Http\Requests\Api\Store\Order;

use Illuminate\Foundation\Http\FormRequest;

class ItemUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_item_id' => ['bail', 'required',],
            'quantity' => ['bail', 'required', 'numeric', 'min:1',],
        ];
    }
}
