<?php

namespace App\Services\Payment\Commands\Bandhan;

use App\Services\Payment\Contracts\PaymentStepCommand;

class PushCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement Bandhan push logic
        
        return [
            'status' => 1,
            'msg' => 'Lot pushed successfully via Bandhan logic.',
            'type' => 'green'
        ];
    }
}
