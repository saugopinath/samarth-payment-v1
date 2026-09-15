<?php

namespace App\Services\Integration\Commands;

use App\Models\PaymentLotMaster;

interface CommandInterface
{
    /**
     * Execute the specific command.
     *
     * @param PaymentLotMaster $lot
     * @param array $data Additional data passed to the command
     * @return array Array with status, msg, and type keys
     */
    public function execute(PaymentLotMaster $lot, array $data = []): array;
}
