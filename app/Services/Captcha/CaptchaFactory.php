<?php

namespace App\Services\Captcha;

use InvalidArgumentException;

class CaptchaFactory
{
    /**
     * Create the configured Captcha service driver instance.
     *
     * @return CaptchaServiceInterface
     * @throws InvalidArgumentException
     */
    public static function make(): CaptchaServiceInterface
    {
        $driver = config('services.captcha.driver', 'mews');

        return match ($driver) {
            'mews' => new MewsCaptchaService(),
            default => throw new InvalidArgumentException("Unsupported Captcha driver: {$driver}"),
        };
    }
}
