<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGatewayInterface;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Define the action map for the specific strategy.
     */
    abstract protected function getActionMap(): array;

    /**
     * Determines the lot status key.
     */
    abstract protected function getStatusKey($lot): string;

    /**
     * Map action key to the corresponding command class.
     */
    abstract protected function resolveCommandClass(string $actionKey): ?string;

    public function getAvailableSteps($lot): array
    {
        $map = $this->getActionMap();
        $statusKey = $this->getStatusKey($lot);

        return $map[$statusKey] ?? [];
    }

    public function executeStep($lot, string $stepKey, array $data = [])
    {
        $commandClass = $this->resolveCommandClass($stepKey);

        if (!$commandClass || !class_exists($commandClass)) {
            return [
                'status' => 0,
                'msg' => 'Action not supported for this gateway.',
                'type' => 'red'
            ];
        }

        $command = app()->make($commandClass);
        return $command->execute($lot, $data);
    }
}
