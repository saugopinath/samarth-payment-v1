<?php

namespace App\Services\Payment;

use App\Services\Payment\Gateways\IfmsApiGateway;
use App\Services\Payment\Gateways\IfmsSftpGateway;
use App\Services\Payment\Gateways\SbiApiGateway;
use App\Services\Payment\Gateways\SbiSftpGateway;
use App\Services\Payment\Gateways\BandhanApiGateway;
use App\Services\Payment\Gateways\BandhanSftpGateway;

class PaymentGatewayFactory
{
    /**
     * Factory method to return the correct integration strategy (SFTP or API).
     * 
     * @param mixed $lot
     * @return Contracts\PaymentGatewayInterface
     */
    public static function make($lot): Contracts\PaymentGatewayInterface
    {
        // This logic determines the gateway based on the lot's payment_mode and integration_type.
        // Assuming $lot has properties or relationships to determine this.
        // For demonstration, we'll use dummy logic that should be replaced with actual DB checks.
        
        $paymentModeCode = $lot->payment_mode;
        // fetch the name from Codemaster
        $paymentModeModel = \App\Models\Codemaster::where('code', $paymentModeCode)->first();
        $paymentModeName = strtolower($paymentModeModel ? $paymentModeModel->short_name ?? $paymentModeModel->name : ($lot->payment_mode ?? 'sbi'));
        $integrationType = strtolower($lot->int_type ?? 'api');

        if (str_contains($paymentModeName, 'ifms')) {
            return $integrationType === 'sftp' ? app()->make(IfmsSftpGateway::class) : app()->make(IfmsApiGateway::class);
        }

        if (str_contains($paymentModeName, 'bandhan')) {
            return $integrationType === 'sftp' ? app()->make(BandhanSftpGateway::class) : app()->make(BandhanApiGateway::class);
        }

        // Default to SBI
        return $integrationType === 'sftp' ? app()->make(SbiSftpGateway::class) : app()->make(SbiApiGateway::class);
    }
}
