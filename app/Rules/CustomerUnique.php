<?php

namespace App\Rules;

use App\Models\Customer;
use App\Models\Store;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerUnique implements ValidationRule
{
    public function __construct(
        protected string $phone
    )
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            Customer::whereStoreId(Store::current()->id)
                ->wherePhone($this->phone)
                ->exists()
        ) {
            $fail('The customer already exists!');
        }
    }
}
