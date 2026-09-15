<?php

namespace App\Services\Payment\Commands\Sbi;

use App\Services\Payment\Contracts\PaymentStepCommand;

class SignLotCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // DEV MOCK: Update lot status to signed
        $lot->cur_status = config('payment_lot.status.sbi.signed');
        $lot->save();
        
        return [
            'status' => 'success',
            'message' => 'Lot signed successfully (Dev Mock).',
            'type' => 'green'
        ];
    }
}
