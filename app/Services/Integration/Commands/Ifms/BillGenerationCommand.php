<?php

namespace App\Services\Integration\Commands\Ifms;

use App\Services\Integration\Commands\CommandInterface;
use App\Models\PaymentLotMaster;
use Illuminate\Support\Facades\Log;

class BillGenerationCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): array
    {
        Log::info("Executing IFMS API BillGenerationCommand for lot: " . $lot->lot_no, $data);
        
        // This is typically called with modal data like sanction number, bill no, etc.
        return [
            'status' => 1,
            'msg' => 'Bill Generation triggered successfully.',
            'type' => 'blue'
        ];
    }
}
