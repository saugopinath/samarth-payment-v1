<?php

namespace App\Services\Contracts;

use App\Models\PaymentLotMaster;
use App\Models\SbiPaymentLotMasterAdditionalInfo;

interface PaymentSBISFTPIntegrationInterface
{
    /**
     * Prepare the payload (e.g., generate XML or JSON) for the target system.
     */
    public function preparePayload(PaymentLotMaster $lotMaster);

    /**
     * Push the prepared payload to the target system (e.g., via SFTP or API).
     */
    public function pushToTarget(PaymentLotMaster $lotMaster);

    /**
     * Check the acknowledgment status from the target system.
     */
    public function checkAcknowledge(PaymentLotMaster $lotMaster);

    /**
     * Check the final response status from the target system.
     */
    public function checkResponse(PaymentLotMaster $lotMaster);
}
