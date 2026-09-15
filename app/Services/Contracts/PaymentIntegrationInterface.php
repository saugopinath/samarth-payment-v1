<?php

namespace App\Services\Contracts;

use App\Models\PaymentLotMaster;

interface PaymentIntegrationInterface
{
    /**
     * Get the list of available actions (buttons) based on the lot's current status.
     * Returns an array of actions, e.g., [['key' => 'push', 'label' => 'Push to API', 'color' => 'blue']]
     *
     * @param PaymentLotMaster $lotMaster
     * @return array
     */
    public function getAvailableActions(PaymentLotMaster $lotMaster): array;

    /**
     * Execute a specific action command.
     *
     * @param PaymentLotMaster $lotMaster
     * @param string $actionKey
     * @param array $data Additional data required for the action
     * @return mixed
     */
    public function executeAction(PaymentLotMaster $lotMaster, string $actionKey, array $data = []);
}
