<?php

namespace App\Services\Payment\Commands\IfmsApi;

use App\Services\Payment\Commands\CommandInterface;
use App\Models\PaymentLotMaster;

class ImportResponseCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): bool
    {
        $lot->update(['cur_status' => 'PLSIFMS_RESP_IMP']);
        return true;
    }
}
