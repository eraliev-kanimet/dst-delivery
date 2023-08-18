<?php

namespace App\Http\Requests\Api\Store\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:' . implode(',', OrderStatus::values())],
        ];
    }
}
