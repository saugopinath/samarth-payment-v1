<?php

namespace App\Services\Integration\SBI;

use App\Models\PaymentLotMaster;
use App\Models\SbiPaymentLotMasterAdditionalInfo;
use App\Services\Integration\AbstractPaymentIntegrationService;

class SbiApiIntegrationService extends AbstractPaymentIntegrationService
{

    protected function getStatusKey(PaymentLotMaster $lotMaster): string
    {
        return (string)$lotMaster->cur_status;
    }

    protected function getActionMap(): array
    {
        return [
            (string)config('payment_lot.status.common.generated') => [
                ['key' => 'sign', 'label' => 'Sign Lot', 'color' => 'green'],
                ['key' => 'defunc', 'label' => 'Defunc Lot', 'color' => 'green']
            ],
            (string)config('payment_lot.status.sbi.signed') => [
                ['key' => 'push', 'label' => 'Push to API', 'color' => 'blue'],
                ['key' => 'defunc', 'label' => 'Defunc Lot', 'color' => 'green']
            ],
            (string)config('payment_lot.status.common.push') => [
                ['key' => 'check_ack', 'label' => 'Check API Acknowledge', 'color' => 'blue']
            ],
            (string)config('payment_lot.status.common.ack') => [
                ['key' => 'check_res', 'label' => 'Check API Response', 'color' => 'purple']
            ],
        ];
    }

    protected function resolveCommandClass(string $actionKey): ?string
    {
        $map = [
            'sign' => \App\Services\Integration\Commands\Sbi\SignLotCommand::class,
            'push' => \App\Services\Integration\Commands\Sbi\PushCommand::class,
            'check_ack' => \App\Services\Integration\Commands\Sbi\CheckAcknowledgeCommand::class,
            'check_res' => \App\Services\Integration\Commands\Sbi\CheckResponseCommand::class,
            'defunc' => \App\Services\Integration\Commands\Common\DefuncLotCommand::class,
        ];

        return $map[$actionKey] ?? null;
    }
}

