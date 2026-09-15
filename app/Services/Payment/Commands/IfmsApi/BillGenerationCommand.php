<?php

namespace App\Services\Payment\Commands\IfmsApi;

use App\Services\Payment\Commands\CommandInterface;
use App\Models\PaymentLotMaster;

class BillGenerationCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): bool
    {
        $lot->update(['cur_status' => 'PLSIFMS_BILL_GEN']);
        return true;
    }
}
