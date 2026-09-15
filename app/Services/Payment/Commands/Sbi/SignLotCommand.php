<?php

namespace App\Services\Payment\Commands\Sbi;

use App\Services\Payment\Contracts\PaymentStepCommand;

class SignLotCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement SBI lot signing logic
        
        return [
            'status' => 1,
            'msg' => 'Lot signed successfully via SBI logic.',
            'type' => 'green'
        ];
    }
}
