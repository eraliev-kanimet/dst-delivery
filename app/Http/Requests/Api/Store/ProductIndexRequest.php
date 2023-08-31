<?php

namespace App\Http\Requests\Api\Store;

use App\Rules\Order\ProductAttrArray;
use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['bail', 'nullable', 'string'],
            'category_id' => ['bail', 'nullable', 'numeric'],
            'limit' => ['bail', 'nullable', 'numeric'],
            'attributes' => ['bail', 'nullable', new ProductAttrArray],
            'attributes.*' => ['bail', 'nullable', 'string']
        ];
    }
}
