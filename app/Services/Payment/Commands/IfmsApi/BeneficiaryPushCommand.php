<?php

namespace App\Services\Payment\Commands\IfmsApi;

use App\Services\Payment\Contracts\PaymentStepCommand;
use App\Models\PaymentLotMaster;

class BeneficiaryPushCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        $lot->update(['cur_status' => 'PLSIFMS_BEN_PUSH']);
        
        return [
            'status' => 1,
            'msg' => 'Beneficiary pushed successfully.',
            'type' => 'green'
        ];
    }
}
