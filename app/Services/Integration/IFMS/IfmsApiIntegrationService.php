<?php

namespace App\Services\Integration\IFMS;

use App\Models\PaymentLotMaster;
use App\Models\SbiPaymentLotMasterAdditionalInfo;
use App\Services\Contracts\PaymentSBIIntegrationInterface;

class IfmsApiIntegrationService implements PaymentSBIIntegrationInterface
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new IfmsApiIntegrationService();
        }
        return self::$instance;
    }

    public function preparePayload(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS API payload generation
        return true;
    }

    public function pushToTarget(SbiPaymentLotMasterAdditionalInfo $lotMasterAdd)
    {
        // TODO: Implement IFMS API push
        return [
            'status' => 1,
            'msg' => 'IFMS API push not yet implemented for Lot - ' . $lotMasterAdd->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkAcknowledge(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS API acknowledge
        return [
            'status' => 1,
            'msg' => 'IFMS API acknowledge not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkResponse(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS API response
        return [
            'status' => 1,
            'msg' => 'IFMS API response not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }
}
