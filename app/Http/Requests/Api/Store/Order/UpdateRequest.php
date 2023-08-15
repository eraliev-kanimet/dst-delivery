<?php

namespace App\Http\Requests\Api\Store\Order;

use App\Enums\DeliveryType;
use App\Enums\PaymentType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        ];
    }

    protected function passedValidationData(): array
    {
        return [
            'delivery_address' => [
                'first_name' => $this->get('first_name'),
                'last_name' => $this->get('last_name'),
                'email' => $this->get('email'),
                'country' => $this->get('country'),
                'city' => $this->get('city'),
                'address' => $this->get('address'),
                'zip' => $this->get('zip'),
            ],
        ];
    }

    protected function passedValidation(): void
    {
        $data = $this->passedValidationData();

        $data['delivery_type'] = $this->get('delivery_type');
        $data['payment_type'] = $this->get('payment_type');

        $this->replace($data);
    }
}
