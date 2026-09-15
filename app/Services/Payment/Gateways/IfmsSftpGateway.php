<?php

namespace App\Services\Payment\Gateways;

class IfmsSftpGateway extends AbstractPaymentGateway
{
    protected function getStatusKey($lot): string
    {
        $status = $lot->cur_status;
        if ($status == config('payment_lot.status.common.generated')) return 'generated';
        if ($status == config('payment_lot.status.common.push')) return 'pushed';
        if ($status == config('payment_lot.status.ifms.dotdone')) return 'dotdone';
        if ($status == config('payment_lot.status.ifms.treasury')) return 'treasury';
        if ($status == config('payment_lot.status.ifms.rbi')) return 'rbi';
        if ($status == config('payment_lot.status.common.defunct')) return 'completed';

        return (string)($status ?? 'generated');
    }

    protected function getActionMap(): array
    {
        return [
            'generated' => [
                ['key' => 'push_ifms', 'label' => 'Push To IFMS (1)', 'color' => 'blue']
            ],
            'pushed' => [
                ['key' => 'check_dotdone', 'label' => 'Check IFMS Ack (2)', 'color' => 'yellow']
            ],
            'dotdone' => [
                ['key' => 'check_treasury', 'label' => 'Check Treasury (3)', 'color' => 'green']
            ],
            'treasury' => [
                ['key' => 'check_rbi', 'label' => 'Import RBI Report (4)', 'color' => 'indigo']
            ],
            'completed' => [
                ['key' => 'defunc', 'label' => 'Defunc Lot (5)', 'color' => 'red']
            ]
        ];
    }

    protected function resolveCommandClass(string $actionKey): ?string
    {
        $map = [
            'push_ifms' => \App\Services\Payment\Commands\Ifms\PushToIfmsCommand::class,
            'check_dotdone' => \App\Services\Payment\Commands\Ifms\IfmsReceivedCommand::class,
            'check_treasury' => \App\Services\Payment\Commands\Ifms\SubmittedToTreasuryCommand::class,
            'check_rbi' => \App\Services\Payment\Commands\Ifms\ImportRbiReportCommand::class,
            'defunc' => \App\Services\Payment\Commands\Common\DefunctLotCommand::class,
        ];
        return $map[$actionKey] ?? null;
    }
}
