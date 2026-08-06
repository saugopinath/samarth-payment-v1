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

        // Get the payment mode string (e.g. 'SBI', 'Bandhan', 'IFMS V3')
        $paymentModeName = strtolower(Codemaster::where('code', $lotMaster->payment_mode)->first()?->name ?? 'sbi');

        if (str_contains($paymentModeName, 'bandhan')) {
            return $integrationType === 'api' ? BandhanApiIntegrationService::getInstance() : BandhanSftpIntegrationService::getInstance();
        } elseif (str_contains($paymentModeName, 'ifms v3')) {
            return $integrationType === 'api' ? IfmsV3ApiIntegrationService::getInstance() : IfmsV3SftpIntegrationService::getInstance();
        } elseif (str_contains($paymentModeName, 'ifms')) {
            return $integrationType === 'api' ? IfmsApiIntegrationService::getInstance() : IfmsSftpIntegrationService::getInstance();
        }

        // Default to SBI
        return $integrationType === 'api' ? SbiApiIntegrationService::getInstance() : SbiSftpIntegrationService::getInstance();
    }
}
