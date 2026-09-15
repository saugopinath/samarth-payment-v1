<?php

namespace App\Services\Integration;

use App\Models\PaymentLotMaster;
use App\Services\Contracts\PaymentIntegrationInterface;

abstract class AbstractPaymentIntegrationService implements PaymentIntegrationInterface
{

    /**
     * Define the action map for the specific strategy.
     * Overridden by subclasses to return configuration.
     */
    abstract protected function getActionMap(): array;

    /**
     * Get the list of available actions (buttons) based on the lot's current status.
     *
     * @param PaymentLotMaster $lotMaster
     * @return array
     */
    public function getAvailableActions(PaymentLotMaster $lotMaster): array
    {
        $map = $this->getActionMap();
        $statusKey = $this->getStatusKey($lotMaster);

        if (isset($map[$statusKey])) {
            return $map[$statusKey];
        }

        return [];
    }

    /**
     * Execute a specific action command.
     *
     * @param PaymentLotMaster $lotMaster
     * @param string $actionKey
     * @param array $data
     * @return mixed
     */
    public function executeAction(PaymentLotMaster $lotMaster, string $actionKey, array $data = [])
    {
        $commandClass = $this->resolveCommandClass($actionKey);

        if (!$commandClass || !class_exists($commandClass)) {
            return [
                'status' => 0,
                'msg' => 'Action not supported for ' . class_basename($this),
                'type' => 'red'
            ];
        }

        $command = app()->make($commandClass);
        return $command->execute($lotMaster, $data);
    }

    /**
     * Determines the lot status key. Can be overridden.
     */
    protected function getStatusKey(PaymentLotMaster $lotMaster): string
    {
        // Convert status to a readable key if necessary
        return (string)$lotMaster->cur_status;
    }

    /**
     * Map action key to the corresponding command class.
     */
    abstract protected function resolveCommandClass(string $actionKey): ?string;
}
