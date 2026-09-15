<?php

namespace App\Services\Payment\Commands\Ifms;

use App\Services\Payment\Contracts\PaymentStepCommand;

class SubmittedToTreasuryCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // DEV MOCK: Update lot status to treasury
        $lot->cur_status = config('payment_lot.status.ifms.treasury');
        $lot->save();
        
        return [
            'status' => 'success',
            'message' => 'Successfully submitted to treasury (Dev Mock).',
            'type' => 'green'
        ];
    }
}
