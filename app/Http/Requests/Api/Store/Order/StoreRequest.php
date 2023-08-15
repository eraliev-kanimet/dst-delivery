<?php

namespace App\Http\Requests\Api\Store\Order;

use App\Models\Selection;
use App\Models\Store;
use App\Rules\Order\ProductArray;

class StoreRequest extends UpdateRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['products'] = ['required', new ProductArray];

        return $rules;
    }

    protected function passedValidation(): void
    {
        $data = $this->passedValidationData();

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

        $data['products'] = $selections;

        $this->merge($data);
    }
}
