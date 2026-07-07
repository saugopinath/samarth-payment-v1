<?php

namespace App\Services\Sms;

interface SmsServiceInterface
{
    /**
     * Send OTP to a mobile number.
     *
     * @param string $mobileNo
     * @param string $otp
     * @return bool
     */
    public function sendOtp(string $mobileNo, string $otp): bool;
}
