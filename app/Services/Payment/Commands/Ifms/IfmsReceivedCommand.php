<?php

namespace App\Services\Payment\Commands\Ifms;

use App\Services\Payment\Contracts\PaymentStepCommand;

class IfmsReceivedCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // DEV MOCK: Update lot status to dotdone
        $lot->cur_status = config('payment_lot.status.ifms.dotdone');
        $lot->save();
        
        return [
            'status' => 'success',
            'message' => 'Received dotdone acknowledgement from IFMS (Dev Mock).',
            'type' => 'green'
        ];
    }
}
