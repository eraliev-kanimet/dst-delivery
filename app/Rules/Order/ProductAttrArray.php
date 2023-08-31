<?php

namespace App\Rules\Order;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductAttrArray implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $message = __('validation.regex', ['attribute' => $attribute]);

        if (!is_array($value)) {
            $fail($message);
        }

        foreach ($value as $key => $quantity) {
            if (!preg_match('/^attribute_id_\d+$/', $key)) {
                $fail($message);
            }
        }
    }
}
