<?php

namespace App\Services\Payment\Commands\Ifms;

use App\Services\Payment\Contracts\PaymentStepCommand;

class SubmittedToTreasuryCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement logic to check if submitted to treasury
        
        return [
            'status' => 1,
            'msg' => 'Successfully submitted to treasury.',
            'type' => 'green'
        ];
    }
}
