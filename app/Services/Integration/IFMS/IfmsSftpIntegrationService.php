<?php

namespace App\Services\Integration\IFMS;

use App\Models\PaymentLotMaster;
use App\Models\SbiPaymentLotMasterAdditionalInfo;
use App\Services\Contracts\PaymentSBIIntegrationInterface;

class IfmsSftpIntegrationService implements PaymentSBIIntegrationInterface
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new IfmsSftpIntegrationService();
        }
        return self::$instance;
    }

    public function preparePayload(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS SFTP payload generation
        return true;
    }

    public function pushToTarget(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS SFTP push
        return [
            'status' => 1,
            'msg' => 'IFMS SFTP push not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkAcknowledge(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS SFTP acknowledge
        return [
            'status' => 1,
            'msg' => 'IFMS SFTP acknowledge not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkResponse(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS SFTP response
        return [
            'status' => 1,
            'msg' => 'IFMS SFTP response not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }
}

