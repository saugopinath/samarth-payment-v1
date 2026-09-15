<?php

namespace App\Services\Integration\Commands\Sbi;

use App\Services\Integration\Commands\CommandInterface;
use App\Models\PaymentLotMaster;

class PushCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): array
    {
        return [
            'status' => 1,
            'msg' => 'Pushed to SBI.',
            'type' => 'blue'
        ];
    }
}
