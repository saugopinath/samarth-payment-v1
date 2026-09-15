<?php

namespace App\Services\Payment\Gateways;

class IfmsApiGateway extends AbstractPaymentGateway
{
    protected function getStatusKey($lot): string
    {
        if ($lot->cur_status == config('payment_lot.status.common.generated', 'generated')) {
            return 'generated';
        }
        return "{$lot->lot_status}_{$lot->bill_status}_{$lot->push_to_ifms_status}";
    }

    protected function getActionMap(): array
    {
        return [
            'generated' => [
                ['key' => 'bill_generation', 'label' => 'Bill Generation (1)', 'color' => 'blue']
            ],
            '1_1_0' => [
                ['key' => 'beneficiary_push', 'label' => 'Beneficiary send To IFMS (2)', 'color' => 'yellow']
            ],
            '0_2_1' => [
                ['key' => 'check_bill_status', 'label' => 'Bill Status Received (3)', 'color' => 'green']
            ],
            '0_3_1' => [
                ['key' => 'receive_response', 'label' => 'Receive Response (4)', 'color' => 'blue']
            ],
            '0_4_1' => [
                ['key' => 'import_response', 'label' => 'Import Response (5)', 'color' => 'yellow']
            ],
            'completed' => [
                ['key' => 'defunc', 'label' => 'Defunc Lot (6)', 'color' => 'red']
            ]
        ];
    }

    protected function resolveCommandClass(string $actionKey): ?string
    {
        $map = [
            'bill_generation' => \App\Services\Payment\Commands\Ifms\BillGenerationCommand::class,
            'beneficiary_push' => \App\Services\Payment\Commands\Ifms\BeneficiaryPushCommand::class,
            'check_bill_status' => \App\Services\Payment\Commands\Ifms\CheckBillStatusCommand::class,
            'receive_response' => \App\Services\Payment\Commands\Ifms\ReceiveResponseCommand::class,
            'import_response' => \App\Services\Payment\Commands\Ifms\ImportResponseCommand::class,
            'defunc' => \App\Services\Payment\Commands\Common\DefunctLotCommand::class,
        ];
        return $map[$actionKey] ?? null;
    }
}
