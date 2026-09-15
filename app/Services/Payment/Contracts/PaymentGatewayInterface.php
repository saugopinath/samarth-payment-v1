<?php

namespace App\Services\Payment\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Get available action steps based on the lot's current status.
     *
     * @param mixed $lot
     * @return array
     */
    public function getAvailableSteps($lot): array;

    /**
     * Execute a specific step by its unique key.
     *
     * @param mixed $lot
     * @param string $stepKey
     * @param array $data
     * @return mixed
     */
    public function executeStep($lot, string $stepKey, array $data = []);
}
