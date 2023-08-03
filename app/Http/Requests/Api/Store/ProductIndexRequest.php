<?php

namespace App\Http\Requests\Api\Store;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string'],
            'category_id' => ['nullable', 'numeric'],
            'limit' => ['nullable', 'numeric'],
            'attributes' => ['nullable', 'array'],
        ];
    }
}
