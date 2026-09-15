<?php

namespace App\Services\Payment\Commands\Common;

use App\Services\Payment\Contracts\PaymentStepCommand;

class CheckAcknowledgeCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // DEV MOCK: Update lot status to acknowledged
        $lot->cur_status = config('payment_lot.status.common.ack');
        $lot->save();
        
        return [
            'status' => 'success',
            'message' => 'Lot acknowledged successfully (Dev Mock).',
            'type' => 'green'
        ];
    }
}
