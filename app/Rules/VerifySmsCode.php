<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

class VerifySmsCode implements ValidationRule
{
    public function __construct(
        protected string $key
    )
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $key = 'sms_code_' . $this->key;

        if (Cache::has($key)) {
            $code = Cache::get($key);

            if ((int) $value == $code) {
                Cache::forget($key);
                return;
            }
        }

        $fail(__('validation2.verify.text1'));
    }
}
