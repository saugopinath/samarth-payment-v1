<?php

namespace App\Services\Payment\Commands\Bandhan;

use App\Services\Payment\Contracts\PaymentStepCommand;

class PushCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // DEV MOCK: Update lot status to pushed
        $lot->cur_status = config('payment_lot.status.common.push');
        $lot->save();
        
        return [
            'status' => 'success',
            'message' => 'Lot pushed successfully via Bandhan (Dev Mock).',
            'type' => 'green'
        ];
    }
}
