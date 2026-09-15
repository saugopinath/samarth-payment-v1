<?php

namespace App\Services\Payment\Commands\Ifms;

use App\Services\Payment\Contracts\PaymentStepCommand;

class ImportRbiReportCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // TODO: Implement logic to import RBI report via SFTP
        
        return [
            'status' => 1,
            'msg' => 'RBI Report successfully imported.',
            'type' => 'green'
        ];
    }
}
