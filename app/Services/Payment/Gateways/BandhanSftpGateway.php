<?php

namespace App\Services\Payment\Gateways;

class BandhanSftpGateway extends AbstractPaymentGateway
{
    protected function getStatusKey($lot): string
    {
        return (string)($lot->cur_status ?? 'generated');
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
