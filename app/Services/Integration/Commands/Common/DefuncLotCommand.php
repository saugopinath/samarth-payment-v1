<?php

namespace App\Services\Integration\Commands\Common;

use App\Services\Integration\Commands\CommandInterface;
use App\Models\PaymentLotMaster;
use Illuminate\Support\Facades\Log;

class DefuncLotCommand implements CommandInterface
{
    public function execute(PaymentLotMaster $lot, array $data = []): array
    {
        // TODO: Implement defunct lot logic
        Log::info("Executing DefuncLotCommand for lot: " . $lot->lot_no);
        
        return [
            'status' => 1,
            'msg' => 'Lot ' . $lot->lot_no . ' defunced successfully.',
            'type' => 'green'
        ];
    }
}
