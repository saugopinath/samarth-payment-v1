<?php

namespace App\Services\Integration;

use App\Models\PaymentLotMaster;
use App\Models\Codemaster;
use App\Services\Integration\SBI\SbiSftpIntegrationService;
use App\Services\Integration\SBI\SbiApiIntegrationService;
use App\Services\Integration\Bandhan\BandhanSftpIntegrationService;
use App\Services\Integration\Bandhan\BandhanApiIntegrationService;
use App\Services\Integration\IFMS\IfmsSftpIntegrationService;
use App\Services\Integration\IFMS\IfmsApiIntegrationService;
use App\Services\Integration\IFMS_V3\IfmsV3SftpIntegrationService;
use App\Services\Integration\IFMS_V3\IfmsV3ApiIntegrationService;

class PaymentIntegrationFactory
{
    /**
     * Strategy map associating payment mode keywords with their respective service classes.
     * Structured as: 'keyword' => ['sftp' => SftpClass, 'api' => ApiClass]
     */
    protected static array $strategies = [
        'bandhan' => [
            'sftp' => BandhanSftpIntegrationService::class,
            'api'  => BandhanApiIntegrationService::class,
        ],
        'ifms v3' => [
            'sftp' => IfmsV3SftpIntegrationService::class,
            'api'  => IfmsV3ApiIntegrationService::class,
        ],
        'ifms' => [
            'sftp' => IfmsSftpIntegrationService::class,
            'api'  => IfmsApiIntegrationService::class,
        ],
        'sbi' => [
            'sftp' => SbiSftpIntegrationService::class,
            'api'  => SbiApiIntegrationService::class,
        ],
    ];

    /**
     * Factory method to return the correct integration strategy (SFTP or API).
     */
    public static function make($lotNo): \App\Services\Contracts\PaymentIntegrationInterface
    {
        $lotMaster = PaymentLotMaster::where('lot_no', $lotNo)->firstOrFail();
        
        $setting = \App\Models\PaymentMainSetting::where('scheme_id', $lotMaster->scheme_id)
            ->where('financial_year', $lotMaster->lot_year)
            ->first();

        $integrationType = 'sftp'; // Default to SFTP
        
        if ($setting) {
            $monthField = strtolower($lotMaster->lot_month);
            $monthData = $setting->$monthField;
            if (is_array($monthData) && isset($monthData['integration_type']) && !empty($monthData['integration_type'])) {
                $integrationType = $monthData['integration_type'];
            }
        }

        $paymentModeName = strtolower(Codemaster::where('code', $lotMaster->payment_mode)->first()?->name ?? 'sbi');
        
        // Find matching strategy
        $selectedStrategy = self::$strategies['sbi']; // Default
        foreach (self::$strategies as $keyword => $strategy) {
            if (str_contains($paymentModeName, $keyword)) {
                $selectedStrategy = $strategy;
                break;
            }
        }

        // Determine the class based on integration type (default to SFTP if API is not found)
        $serviceClass = $selectedStrategy[$integrationType] ?? $selectedStrategy['sftp'];

        // Use Laravel's container to resolve the dependency
        return app()->make($serviceClass);
    }
}
