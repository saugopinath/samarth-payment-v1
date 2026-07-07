<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsService implements SmsServiceInterface
{
    public function sendOtp(string $mobileNo, string $otp): bool
    {
        Log::info("Sending OTP via SMS driver [log]. Mobile: {$mobileNo}, OTP: {$otp}");
        Log::info("DEVELOPMENT OTP: Your verification code is: {$otp}");
        
        return true;
    }
}
