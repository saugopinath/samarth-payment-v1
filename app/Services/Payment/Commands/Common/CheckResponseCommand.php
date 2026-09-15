<?php

namespace App\Services\Payment\Commands\Common;

use App\Services\Payment\Contracts\PaymentStepCommand;

class CheckResponseCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement common check response logic
        
        return [
            'status' => 1,
            'msg' => 'Response checked successfully.',
            'type' => 'green'
        ];
    }
}
