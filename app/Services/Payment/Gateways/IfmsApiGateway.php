<?php

namespace App\Services\Payment\Gateways;

class IfmsApiGateway extends AbstractPaymentGateway
{
    protected function getStatusKey($lot): string
    {
        $status = $lot->cur_status;
        if ($status == config('payment_lot.status.common.generated')) return 'generated';
        if ($status == 'PLSIFMS_BILL_GEN') return 'bill_generated';
        if ($status == 'PLSIFMS_BEN_PUSH') return 'ben_pushed';
        if ($status == 'PLSIFMS_BILL_STATUS') return 'bill_status_received';
        if ($status == 'PLSIFMS_RESP_RECV') return 'response_received';
        if ($status == 'PLSIFMS_RESP_IMP') return 'response_imported';
        if ($status == config('payment_lot.status.common.defunct')) return 'completed';

        return (string)($status ?? 'generated');
    }

    protected function getActionMap(): array
    {
        return [
            'generated' => [
                ['key' => 'bill_generation', 'label' => 'Bill Generation (1)', 'color' => 'blue']
            ],
            'bill_generated' => [
                ['key' => 'beneficiary_push', 'label' => 'Beneficiary send To IFMS (2)', 'color' => 'yellow']
            ],
            'ben_pushed' => [
                ['key' => 'check_bill_status', 'label' => 'Bill Status Received (3)', 'color' => 'green']
            ],
            'bill_status_received' => [
                ['key' => 'receive_response', 'label' => 'Receive Response (4)', 'color' => 'blue']
            ],
            'response_received' => [
                ['key' => 'import_response', 'label' => 'Import Response (5)', 'color' => 'yellow']
            ],
            'response_imported' => [
                ['key' => 'defunc', 'label' => 'Defunc Lot (6)', 'color' => 'red']
            ]
        ];
    }

    protected function resolveCommandClass(string $actionKey): ?string
    {
        $map = [
            'bill_generation' => \App\Services\Payment\Commands\IfmsApi\BillGenerationCommand::class,
            'beneficiary_push' => \App\Services\Payment\Commands\IfmsApi\BeneficiaryPushCommand::class,
            'check_bill_status' => \App\Services\Payment\Commands\IfmsApi\CheckBillStatusCommand::class,
            'receive_response' => \App\Services\Payment\Commands\IfmsApi\ReceiveResponseCommand::class,
            'import_response' => \App\Services\Payment\Commands\IfmsApi\ImportResponseCommand::class,
            'defunc' => \App\Services\Payment\Commands\Common\DefunctLotCommand::class,
        ];
        return $map[$actionKey] ?? null;
    }
}
