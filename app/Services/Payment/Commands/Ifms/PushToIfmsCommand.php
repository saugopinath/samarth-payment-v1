<?php

namespace App\Services\Payment\Commands\Ifms;

use App\Services\Payment\Contracts\PaymentStepCommand;

class PushToIfmsCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement SFTP push logic to IFMS
        
        return [
            'status' => 1,
            'msg' => 'Successfully pushed to IFMS.',
            'type' => 'green'
        ];
    }
}
