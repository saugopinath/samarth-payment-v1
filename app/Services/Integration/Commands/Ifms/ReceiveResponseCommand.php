<?php

namespace App\Services\Integration\Commands\Ifms;

use App\Services\Integration\Commands\CommandInterface;
use App\Models\PaymentLotMaster;

class ReceiveResponseCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): array
    {
        return [
            'status' => 1,
            'msg' => 'Response received from IFMS.',
            'type' => 'blue'
        ];
    }
}
