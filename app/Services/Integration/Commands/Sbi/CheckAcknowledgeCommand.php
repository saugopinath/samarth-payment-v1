<?php

namespace App\Services\Integration\Commands\Sbi;

use App\Services\Integration\Commands\CommandInterface;
use App\Models\PaymentLotMaster;

class CheckAcknowledgeCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): array
    {
        return [
            'status' => 1,
            'msg' => 'Acknowledge checked from SBI.',
            'type' => 'blue'
        ];
    }
}
