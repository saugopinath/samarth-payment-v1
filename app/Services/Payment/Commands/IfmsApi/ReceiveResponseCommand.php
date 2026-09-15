<?php

namespace App\Services\Payment\Commands\IfmsApi;

use App\Services\Payment\Contracts\PaymentStepCommand;
use App\Models\PaymentLotMaster;

class ReceiveResponseCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        $lot->update(['cur_status' => 'PLSIFMS_RESP_RECV']);
        
        return [
            'status' => 1,
            'msg' => 'Response received successfully.',
            'type' => 'green'
        ];
    }
}
