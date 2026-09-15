<?php

namespace App\Services\Payment\Gateways;

class IfmsSftpGateway extends AbstractPaymentGateway
{
    protected function getStatusKey($lot): string
    {
        // For SFTP, we might rely on different status codes, but we map them to string keys for clarity
        return (string)($lot->cur_status ?? 'generated');
    }

    protected function getActionMap(): array
    {
        return [
            'generated' => [
                ['key' => 'push_to_ifms', 'label' => 'Push to IFMS (1)', 'color' => 'blue']
            ],
            'pushed' => [
                ['key' => 'ifms_received', 'label' => 'IFMS Received (2)', 'color' => 'purple']
            ],
            'received' => [
                ['key' => 'submitted_to_treasury', 'label' => 'Submitted To Treasury (3)', 'color' => 'indigo']
            ],
            'treasury_submitted' => [
                ['key' => 'import_rbi_report', 'label' => 'Import RBI Report (4)', 'color' => 'yellow']
            ],
            'completed' => [
                ['key' => 'defunc', 'label' => 'Defunc Lot (5)', 'color' => 'red']
            ]
        ];
    }

    protected function resolveCommandClass(string $actionKey): ?string
    {
        $map = [
            'push_to_ifms' => \App\Services\Payment\Commands\Ifms\PushToIfmsCommand::class,
            'ifms_received' => \App\Services\Payment\Commands\Ifms\IfmsReceivedCommand::class,
            'submitted_to_treasury' => \App\Services\Payment\Commands\Ifms\SubmittedToTreasuryCommand::class,
            'import_rbi_report' => \App\Services\Payment\Commands\Ifms\ImportRbiReportCommand::class,
            'defunc' => \App\Services\Payment\Commands\Common\DefunctLotCommand::class,
        ];
        return $map[$actionKey] ?? null;
    }
}
