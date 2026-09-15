<?php

namespace App\Services\Payment\Commands\IfmsApi;

use App\Services\Payment\Contracts\PaymentStepCommand;
use App\Models\PaymentLotMaster;

class CheckBillStatusCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        $lot->update(['cur_status' => 'PLSIFMS_BILL_STATUS']);
        
        return [
            'status' => 1,
            'msg' => 'Bill status received successfully.',
            'type' => 'green'
        ];
    }
}
