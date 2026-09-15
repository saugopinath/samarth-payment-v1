<?php

namespace App\Services\Payment\Commands\Sbi;

use App\Services\Payment\Contracts\PaymentStepCommand;

class PushCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement SBI push logic
        
        return [
            'status' => 1,
            'msg' => 'Lot pushed successfully via SBI logic.',
            'type' => 'green'
        ];
    }
}
