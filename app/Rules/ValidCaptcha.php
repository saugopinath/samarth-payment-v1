<?php

namespace App\Rules;

use App\Services\Captcha\CaptchaFactory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidCaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $captchaService = CaptchaFactory::make();
        
        if (! $captchaService->validate((string) $value)) {
            $fail('The :attribute is incorrect.');
        }
    }
}
