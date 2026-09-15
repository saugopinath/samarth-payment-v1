<?php

namespace App\Services\Integration\Bandhan;

use App\Models\PaymentLotMaster;
use App\Services\Integration\AbstractPaymentIntegrationService;

class BandhanApiIntegrationService extends AbstractPaymentIntegrationService
{
    public function preparePayload(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement Bandhan API payload generation
        return true;
    }

    public function pushToTarget(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement Bandhan API push
        return [
            'status' => 1,
            'msg' => 'Bandhan API push not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }
}
