<?php

namespace App\Services\Integration\IFMS_V3;

use App\Models\PaymentLotMaster;
use App\Models\SbiPaymentLotMasterAdditionalInfo;
use App\Services\Integration\AbstractPaymentIntegrationService;

class IfmsV3ApiIntegrationService extends AbstractPaymentIntegrationService
{

    public function preparePayload(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS V3 API payload generation
        return true;
    }

    public function pushToTarget(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS V3 API push
        return [
            'status' => 1,
            'msg' => 'IFMS V3 API push not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkAcknowledge(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS V3 API acknowledge
        return [
            'status' => 1,
            'msg' => 'IFMS V3 API acknowledge not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkResponse(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS V3 API response
        return [
            'status' => 1,
            'msg' => 'IFMS V3 API response not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }
}

