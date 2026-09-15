<?php

namespace App\Services\Payment\Commands\Ifms;

use App\Services\Payment\Contracts\PaymentStepCommand;

class IfmsReceivedCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement IFMS receive acknowledgement logic
        
        return [
            'status' => 1,
            'msg' => 'Received acknowledgement from IFMS.',
            'type' => 'green'
        ];
    }
}
