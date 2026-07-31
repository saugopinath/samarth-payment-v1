<?php

namespace App\Services;

use App\Contracts\Repositories\PaymentLotRepositoryInterface;
use App\Models\PaymentLotMaster;

class PaymentLotService
{
    protected $paymentLotRepository;

    public function __construct(PaymentLotRepositoryInterface $paymentLotRepository)
    {
        $this->paymentLotRepository = $paymentLotRepository;
    }

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
    ): void {
        $this->paymentLotRepository->generateTransactionLot(
            $lotMaster,
            $schemeId,
            $financialYear,
            $lotMonth,
            $paymentType,
            $targetPaymentMode,
            $filters
        );
    }

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
    ): array {
        return $this->paymentLotRepository->previewTransactionLot(
            $schemeId,
            $financialYear,
            $lotMonth,
            $paymentType,
            $targetPaymentMode,
            $lotTypeId,
            $filters
        );
    }
}
