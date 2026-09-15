<?php

namespace App\Services\Integration\Commands\Ifms;

use App\Services\Integration\Commands\CommandInterface;
use App\Models\PaymentLotMaster;

class ImportResponseCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): array
    {
        return [
            'status' => 1,
            'msg' => 'Response imported from RBI.',
            'type' => 'blue'
        ];
    }
}
