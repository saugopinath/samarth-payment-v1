<?php

namespace App\Services\Integration\Bandhan;

use App\Models\PaymentLotMaster;
use App\Models\SbiPaymentLotMasterAdditionalInfo;
use App\Services\Contracts\PaymentSBIIntegrationInterface;

class BandhanSftpIntegrationService implements PaymentSBIIntegrationInterface
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new BandhanSftpIntegrationService();
        }
        return self::$instance;
    }

    public function preparePayload(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement Bandhan SFTP payload generation
        return true;
    }

    public function pushToTarget(SbiPaymentLotMasterAdditionalInfo $lotMasterAdd)
    {
        // TODO: Implement Bandhan SFTP push
        return [
            'status' => 1,
            'msg' => 'Bandhan SFTP push not yet implemented for Lot - ' . $lotMasterAdd->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkAcknowledge(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement Bandhan SFTP acknowledge
        return [
            'status' => 1,
            'msg' => 'Bandhan SFTP acknowledge not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkResponse(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement Bandhan SFTP response
        return [
            'status' => 1,
            'msg' => 'Bandhan SFTP response not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }
}
