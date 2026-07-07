<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ApiSmsService implements SmsServiceInterface
{
    public function sendOtp(string $mobileNo, string $otp): bool
    {
        Log::info("Sending OTP via SMS driver [api]. Mobile: {$mobileNo}, OTP: {$otp}");
        
        $apiUrl = config('services.sms.api_url');
        $apiKey = config('services.sms.api_key');
        $senderId = config('services.sms.sender_id');

        if (empty($apiUrl)) {
            Log::warning("SMS API url is not configured. Falling back to log.");
            return true;
        }

        try {
            // Example API call to SMS gateway
            $response = Http::post($apiUrl, [
                'api_key' => $apiKey,
                'to' => $mobileNo,
                'message' => "Your OTP is {$otp}. It is valid for 10 minutes.",
                'sender' => $senderId,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$mobileNo}");
                return true;
            }

            Log::error("Failed to send SMS to {$mobileNo}. Status: " . $response->status() . " Response: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("SMS Gateway Exception: " . $e->getMessage());
            return false;
        }
    }
}
