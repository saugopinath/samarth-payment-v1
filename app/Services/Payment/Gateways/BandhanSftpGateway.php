<?php

namespace App\Services\Payment\Gateways;

class BandhanSftpGateway extends AbstractPaymentGateway
{
    protected function getStatusKey($lot): string
    {
        $status = $lot->cur_status;
        if ($status == config('payment_lot.status.common.generated')) return 'generated';
        if ($status == config('payment_lot.status.common.push')) return 'pushed';
        if ($status == config('payment_lot.status.common.ack')) return 'ack_received';
        if ($status == config('payment_lot.status.common.response')) return 'response_received';
        if ($status == config('payment_lot.status.common.defunct')) return 'completed';
        
        return (string)($status ?? 'generated');
    }

    protected function getActionMap(): array
    {
        return [
            'generated' => [
                ['key' => 'push', 'label' => 'Push (1)', 'color' => 'blue']
            ],
            'pushed' => [
                ['key' => 'check_ack', 'label' => 'Check Acknowledge (2)', 'color' => 'purple']
            ],
            'ack_received' => [
                ['key' => 'check_res', 'label' => 'Check Response (3)', 'color' => 'indigo']
            ],
            'completed' => [
                ['key' => 'defunc', 'label' => 'Defunc Lot (4)', 'color' => 'red']
            ]
        ];
    }

    protected function resolveCommandClass(string $actionKey): ?string
    {
        $map = [
            'push' => \App\Services\Payment\Commands\Bandhan\PushCommand::class,
            'check_ack' => \App\Services\Payment\Commands\Common\CheckAcknowledgeCommand::class,
            'check_res' => \App\Services\Payment\Commands\Common\CheckResponseCommand::class,
            'defunc' => \App\Services\Payment\Commands\Common\DefunctLotCommand::class,
        ];
        return $map[$actionKey] ?? null;
    }
}
