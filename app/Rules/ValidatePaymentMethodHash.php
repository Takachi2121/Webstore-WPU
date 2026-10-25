<?php

namespace App\Rules;

use App\Services\PaymentMethodQueryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidatePaymentMethodHash implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = app(PaymentMethodQueryService::class);

        $found = $query->getPaymentMethodByHash($value);

        if (!$found) {
            $fail('The selected payment method is invalid.');
        }
    }
}
