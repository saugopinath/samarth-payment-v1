<?php

namespace App\Services\Otp;

class OtpGenerator
{
    /**
     * Generate a 6-digit OTP code.
     * In production it generates a random 6-digit number.
     * In other environments it defaults to '123456'.
     */
    public function generate(): string
    {
        return app()->environment('production') ? strval(rand(100000, 999999)) : '123456';
    }
}
