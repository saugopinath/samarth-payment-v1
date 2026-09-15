<?php

namespace App\Services\Integration\Commands\Ifms;

use App\Services\Integration\Commands\CommandInterface;
use App\Models\PaymentLotMaster;

class CheckBillStatusCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): array
    {
        return [
            'status' => 1,
            'msg' => 'Bill Status checked.',
            'type' => 'blue'
        ];
    }
}
