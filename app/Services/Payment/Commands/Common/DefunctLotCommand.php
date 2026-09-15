<?php

namespace App\Services\Payment\Commands\Common;

use App\Services\Payment\Contracts\PaymentStepCommand;

class DefunctLotCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement actual logic to mark the lot as defunct in the database
        
        return [
            'status' => 1,
            'msg' => 'Lot successfully marked as defunct.',
            'type' => 'green'
        ];
    }
}
