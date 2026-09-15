<?php

namespace App\Services\Payment\Commands\Ifms;

use App\Services\Payment\Contracts\PaymentStepCommand;

class PushToIfmsCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // DEV MOCK: Update lot status to pushed
        $lot->cur_status = config('payment_lot.status.common.push');
        $lot->save();
        
        return [
            'status' => 'success',
            'message' => 'Successfully pushed to IFMS (Dev Mock).',
            'type' => 'green'
        ];
    }
}
