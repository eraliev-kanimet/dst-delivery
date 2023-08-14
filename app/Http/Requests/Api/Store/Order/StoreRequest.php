<?php

namespace App\Http\Requests\Api\Store\Order;

use App\Enums\DeliveryType;
use App\Enums\PaymentType;
use App\Models\Selection;
use App\Models\Store;
use App\Rules\Order\ProductArray;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'delivery_type' => ['bail', 'required', 'in:' . implode(',', DeliveryType::values())],
            'payment_type' => ['bail', 'required', 'in:' . implode(',', PaymentType::values())],

            'first_name' => ['bail', 'required'],
            'last_name' => ['bail', 'required'],
            'email' => ['bail', 'required', 'email'],
            'country' => ['bail', 'required'],
            'city' => ['bail', 'required'],
            'address' => ['bail', 'required'],
            'zip' => ['bail', 'required'],

            'products' => ['required', new ProductArray],
        ];
    }

    protected function passedValidation(): void
    {
        $products = [];

        foreach ($this->get('products') as $key => $value) {
            $products[explode('_', $key)[2]] = $value;
        }

        $selections = Selection::whereRelation('product', 'store_id', Store::current()->id)
            ->whereIn('id', array_keys($products))
            ->get(['id', 'price']);

        foreach ($selections as $selection) {
            $selection->quantity = $products[$selection->id];
        }

        $this->merge([
            'delivery_address' => [
                'first_name' => $this->get('first_name'),
                'last_name' => $this->get('last_name'),
                'email' => $this->get('email'),
                'country' => $this->get('country'),
                'city' => $this->get('city'),
                'address' => $this->get('address'),
                'zip' => $this->get('zip'),
            ],
            'products' => $selections,
        ]);
    }
}
