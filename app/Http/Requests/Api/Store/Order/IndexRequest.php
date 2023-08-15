<?php

namespace App\Http\Requests\Api\Store\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'withProduct' => ['nullable', 'in:0,1'],
            'limit' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:' . implode(',', OrderStatus::values())],
        ];
    }
}
