<?php

namespace App\Services\Payment\Contracts;

interface PaymentStepCommand
{
    /**
     * Execute the specific command.
     *
     * @param mixed $lot
     * @param array $data Additional data passed to the command
     * @return array Array with status, msg, and type keys
     */
    public function execute($lot, array $data = []): array;
}
