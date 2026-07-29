<?php

namespace App\Contracts\Repositories;

use App\Models\PaymentLotMaster;

interface PaymentLotRepositoryInterface
{
    /**
     * Generate the transaction lot records for the given payment mode.
     *
     * @param PaymentLotMaster $lotMaster
     * @param int $schemeId
     * @param string $financialYear
     * @param string $lotMonth
     * @param string $paymentType
     * @param string $targetPaymentMode
     * @param array $filters Optional geographical filters
     * @return void
     */
    public function generateTransactionLot(
        PaymentLotMaster $lotMaster,
        int $schemeId,
        string $financialYear,
        string $lotMonth,
        string $paymentType,
        string $targetPaymentMode,
        array $filters = []
    ): void;

    /**
     * Preview the transaction lot records for the given criteria.
     *
     * @param int $schemeId
     * @param string $financialYear
     * @param string $lotMonth
     * @param string $paymentType
     * @param string $targetPaymentMode
     * @param string $lotTypeId
     * @param array $filters Optional geographical filters
     * @return array Contains 'beneficiary_count' and 'total_amount'
     */
    public function previewTransactionLot(
        int $schemeId,
        string $financialYear,
        string $lotMonth,
        string $paymentType,
        string $targetPaymentMode,
        string $lotTypeId,
        array $filters = []
    ): array;
}
