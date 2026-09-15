<?php

namespace App\Services\Payment\Commands\Common;

use App\Services\Payment\Contracts\PaymentStepCommand;

class CheckAcknowledgeCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement common check acknowledgement logic
        
        return [
            'status' => 1,
            'msg' => 'Acknowledgement checked successfully.',
            'type' => 'green'
        ];
    }
}
