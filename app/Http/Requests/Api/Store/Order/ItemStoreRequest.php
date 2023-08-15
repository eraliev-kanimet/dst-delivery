<?php

namespace App\Http\Requests\Api\Store\Order;

use Illuminate\Foundation\Http\FormRequest;

class ItemStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => ['bail', 'required',],
            'selection_id' => ['bail', 'required',],
            'quantity' => ['bail', 'required', 'numeric', 'min:1',],
        ];
    }
}
