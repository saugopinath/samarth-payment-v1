<?php

namespace App\Services\Payment\Commands\Common;

use App\Services\Payment\Contracts\PaymentStepCommand;

class CheckResponseCommand implements PaymentStepCommand
{
    public function execute($lot, array $data = []): array
    {
        // DEV MOCK: Update lot status to response
        $lot->cur_status = config('payment_lot.status.common.response');
        
        // Randomly simulate success and failure if total count exists
        if ($lot->ben_count > 0) {
            // Randomly assign 90-95% as success, remainder as failed
            $successPercentage = rand(90, 95) / 100;
            $lot->success_count = (int) ($lot->ben_count * $successPercentage);
            $lot->failed_count = $lot->ben_count - $lot->success_count;
            
            // Apportion amounts
            if ($lot->total_amount > 0) {
                $avgAmount = $lot->total_amount / $lot->ben_count;
                $lot->success_amount = $lot->success_count * $avgAmount;
                $lot->failed_amount = $lot->total_amount - $lot->success_amount;
            }
        }
        
        $lot->save();
        
        return [
            'status' => 'success',
            'message' => 'Response checked successfully (Dev Mock).',
            'type' => 'green'
        ];
    }
}
