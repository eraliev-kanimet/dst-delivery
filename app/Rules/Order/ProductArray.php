<?php

namespace App\Rules\Order;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductArray implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $message = __('validation.regex', ['attribute' => $attribute]);

        if (!is_array($value)) {
            $fail($message);
        }

        foreach ($value as $key => $quantity) {
            if (!preg_match('/^selection_id_\d+$/', $key)) {
                $fail($message);
            }

            $quantityInteger = (int) $quantity;

            if (strlen($quantity) != strlen($quantityInteger) || $quantity < 1) {
                $fail($message);
            }
        }
    }
}
