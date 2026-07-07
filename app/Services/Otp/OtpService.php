<?php

namespace App\Services\Otp;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\Sms\SmsFactory;
use Illuminate\Support\Facades\DB;

class OtpService
{
    protected OtpGenerator $generator;

    public function __construct(OtpGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generate and dispatch an OTP to the given user.
     * Returns true on success, false on failure.
     */
    public function sendOtp(User $user): bool
    {
        $otp = $this->generator->generate();
        $expireAt = now()->addMinutes(10);

        try {
            DB::beginTransaction();

            $userUpdated = $user->update([
                'last_otp' => $otp,
                'last_otp_generation_time' => now(),
                'last_otp_expire_time' => $expireAt,
            ]);

            $verificationCode = VerificationCode::create([
                'user_id' => $user->id,
                'otp' => $otp,
                'mobile_no' => $user->mobile_no,
                'expire_at' => $expireAt,
            ]);

            $smsService = SmsFactory::make();
            $smsSent = $smsService->sendOtp($user->mobile_no, $otp);

            if ($userUpdated && $verificationCode && $smsSent) {
                DB::commit();
                return true;
            }

            DB::rollBack();
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function verifyOtp(User $user, string $otp): bool
    {
        // Check verification_codes table for matching unexpired OTP
        $verification = VerificationCode::where('user_id', $user->id)
            ->where('otp', $otp)
            ->where('expire_at', '>', now())
            ->where('status', 'pending')
            ->first();

        if ($verification) {
            // Mark OTP as verified instead of deleting
            $verification->update(['status' => 'verified']);
            return true;
        }

        // Fallback check User table directly
        if ($user->last_otp === $otp && $user->last_otp_expire_time && now()->lt($user->last_otp_expire_time)) {
            return true;
        }

        return false;
    }
}
