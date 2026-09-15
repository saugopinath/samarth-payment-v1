<?php

namespace App\Services\Payment\Gateways;

class SbiApiGateway extends AbstractPaymentGateway
{
    protected function getStatusKey($lot): string
    {
        $status = $lot->cur_status;
        if ($status == config('payment_lot.status.common.generated')) return 'generated';
        if ($status == config('payment_lot.status.sbi.signed')) return 'signed';
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
                ['key' => 'sign', 'label' => 'Sign (1)', 'color' => 'green']
            ],
            'signed' => [
                ['key' => 'push', 'label' => 'Push (2)', 'color' => 'blue']
            ],
            'pushed' => [
                ['key' => 'check_ack', 'label' => 'Check Acknowledge (3)', 'color' => 'purple']
            ],
            'ack_received' => [
                ['key' => 'check_res', 'label' => 'Check Response (4)', 'color' => 'indigo']
            ],
            'completed' => [
                ['key' => 'defunc', 'label' => 'Defunc Lot (5)', 'color' => 'red']
            ]
        ];
    }

    protected function resolveCommandClass(string $actionKey): ?string
    {
        $map = [
            'sign' => \App\Services\Payment\Commands\Sbi\SignLotCommand::class,
            'push' => \App\Services\Payment\Commands\Sbi\PushCommand::class,
            'check_ack' => \App\Services\Payment\Commands\Common\CheckAcknowledgeCommand::class,
            'check_res' => \App\Services\Payment\Commands\Common\CheckResponseCommand::class,
            'defunc' => \App\Services\Payment\Commands\Common\DefunctLotCommand::class,
        ];
        return $map[$actionKey] ?? null;
    }
}
