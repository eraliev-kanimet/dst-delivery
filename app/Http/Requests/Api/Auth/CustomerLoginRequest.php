<?php

namespace App\Http\Requests\Api\Auth;

use App\Rules\VerifySmsCode;
use Illuminate\Foundation\Http\FormRequest;

class CustomerLoginRequest extends FormRequest
{
    public function rules(): array
    {
        $phone = $this->get('phone_code', '') . $this->get('phone_number', '');

        return [
            'phone_code' => ['bail', 'required', 'regex:/^\+\d{1,5}$/'],
            'phone_number' => ['bail', 'required', 'numeric', 'max_digits:15'],
            'sms_code' => ['bail', 'required', new VerifySmsCode($phone),],
        ];
    }

    protected function passedValidation(): void
    {
        $this->replace([
            'phone' => $this->get('phone_code') . $this->get('phone_number'),
            'sms_code' => $this->get('sms_code'),
        ]);
    }
}
