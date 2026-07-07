<?php

namespace App\Services\Sms;

use InvalidArgumentException;

class SmsFactory
{
    /**
     * Create the configured SMS service driver instance.
     *
     * @return SmsServiceInterface
     * @throws InvalidArgumentException
     */
    public static function make(): SmsServiceInterface
    {
        $driver = config('services.sms.driver', 'log');

        return match ($driver) {
            'api' => new ApiSmsService(),
            'log' => new LogSmsService(),
            default => throw new InvalidArgumentException("Unsupported SMS driver: {$driver}"),
        };
    }
}
