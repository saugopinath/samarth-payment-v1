<?php

namespace App\Repositories;

use App\Contracts\Repositories\PaymentLotRepositoryInterface;
use App\Models\PaymentLotMaster;
use App\Models\PaymentMainSetting;
use App\Models\BenPaymentDetail;
use App\Models\SbiTransactionLotDetail;
use App\Models\SbiPaymentLotMasterAdditionalInfo;

class PaymentLotRepository implements PaymentLotRepositoryInterface
{
    /**
     * Map of target payment modes to their respective handler methods.
     *
     * @var array<string, string>
     */
    protected array $modeHandlers = [
        '5201' => 'handleSbiTransactionLot',
        '5202' => 'handleIfmsTransactionLot',
        // Add more target payment modes and handlers here as needed
    ];

    /**
     * Generate the transaction lot records for the given payment mode.
     *
     * @param PaymentLotMaster $lotMaster
     * @param int $schemeId
     * @param string $financialYear
     * @param string $lotMonth
     * @param string $paymentType
     * @param string $targetPaymentMode
     * @param array $filters
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
        if (!isset($this->modeHandlers[$targetPaymentMode])) {
            return; // Or throw an exception for unsupported mode
        }

        $handler = $this->modeHandlers[$targetPaymentMode];
        $this->$handler($lotMaster, $schemeId, $financialYear, $lotMonth, $paymentType, $filters);
    }

    /**
     * Handle the generation of SBI transaction lot records.
     *
     * @param PaymentLotMaster $lotMaster
     * @param int $schemeId
     * @param string $financialYear
     * @param string $lotMonth
     * @param string $paymentType
     * @param array $filters
     * @return void
     */
    protected function handleSbiTransactionLot(
        PaymentLotMaster $lotMaster,
        int $schemeId,
        string $financialYear,
        string $lotMonth,
        string $paymentType,
        array $filters = []
    ): void {
        $amountRs = 0;
        $setting = PaymentMainSetting::where('scheme_id', $schemeId)
            ->where('financial_year', $financialYear)
            ->first();

        if ($setting) {
            $monthField = strtolower($lotMonth);
            $monthData = $setting->$monthField;
            if (is_array($monthData) && isset($monthData['amount'])) {
                $amountRs = (float) $monthData['amount'];
            }
            if (is_array($monthData) && isset($monthData['aadhar_type'])) {
                $aadharType = $monthData['aadhar_type'];
            }
        }

        $benDetailsQuery = $this->getBaseBeneficiaryQuery($schemeId, $paymentType, $filters, $lotMonth, $financialYear, true);

        $this->applyLotControlFilters($benDetailsQuery, $lotMaster);

        if (!empty($filters['limit'])) {
            $benDetailsQuery->limit((int) $filters['limit']);
        }

        $benDetails = $benDetailsQuery->get();
        $sbiData = [];
        $debitReference = 'WB003' . \Carbon\Carbon::parse($lotMaster->created_at)->format('dmY') . str_pad($lotMaster->lot_no, 4, '0', STR_PAD_LEFT);

        foreach ($benDetails as $ben) {
            $sbiData[] = [
                'lot_no' => $lotMaster->lot_no,
                'lot_year' => $financialYear,
                'scheme_id' => $schemeId,
                'ben_id' => $ben->ben_id,
                'ben_name' => $ben->ben_name,
                'ifsc' => $paymentType === '5001' ? ($ben->ifsc ?? null) : null,
                'accno' => $paymentType === '5001' ? ($ben->accno ?? null) : null,
                'aadhar_no' => $paymentType === '5002' ? (($aadharType ?? 'raw') === 'token' ? ($ben->aadhar_token ?? null) : ($ben->aadhar_no ?? null)) : null,
                'amount_rs' => $amountRs,
                'debit_reference' => $debitReference,
                'agency_cr_ref' => $ben->created_by_dist_code . $ben->scheme_id . $ben->ben_id,
            ];
        }

        foreach (array_chunk($sbiData, 500) as $chunk) {
            SbiTransactionLotDetail::insert($chunk);
        }

        $lotMaster->update([
            'ben_count' => count($sbiData),
            'total_amount' => count($sbiData) * $amountRs,
        ]);
        SbiPaymentLotMasterAdditionalInfo::create([
                    'lot_no' => $lotMaster->lot_no,
                    'lot_year' => $lotMaster->lot_year,
                    'scheme_id' => $lotMaster->scheme_id,
                    'debit_reference' => $debitReference
        ]);
    }

        /**
     * Handle the generation of IFMS transaction lot records.
     *
     * @param PaymentLotMaster $lotMaster
     * @param int $schemeId
     * @param string $financialYear
     * @param string $lotMonth
     * @param string $paymentType
     * @param array $filters
     * @return void
     */
    protected function handleIfmsTransactionLot(
        PaymentLotMaster $lotMaster,
        int $schemeId,
        string $financialYear,
        string $lotMonth,
        string $paymentType,
        array $filters = []
    ): void {
        $amountRs = 0;
        $setting = PaymentMainSetting::where('scheme_id', $schemeId)
            ->where('financial_year', $financialYear)
            ->first();

        if ($setting) {
            $monthField = strtolower($lotMonth);
            $monthData = $setting->$monthField;
            if (is_array($monthData) && isset($monthData['amount'])) {
                $amountRs = (float) $monthData['amount'];
            }
            if (is_array($monthData) && isset($monthData['aadhar_type'])) {
                $aadharType = $monthData['aadhar_type'];
            }
        }

        $benDetailsQuery = $this->getBaseBeneficiaryQuery($schemeId, $paymentType, $filters, $lotMonth, $financialYear, true);

        $this->applyLotControlFilters($benDetailsQuery, $lotMaster);

        if (!empty($filters['limit'])) {
            $benDetailsQuery->limit((int) $filters['limit']);
        }

        $benDetails = $benDetailsQuery->get();
        $sbiData = [];

        foreach ($benDetails as $ben) {
            $sbiData[] = [
                'lot_no' => $lotMaster->lot_no,
                'lot_year' => $financialYear,
                'scheme_id' => $schemeId,
                'ben_id' => $ben->ben_id,
                'ben_name' => $ben->ben_name,
                'ifsc' => $paymentType === '5001' ? ($ben->ifsc ?? null) : null,
                'accno' => $paymentType === '5001' ? ($ben->accno ?? null) : null,
                'aadhar_no' => $paymentType === '5002' ? (($aadharType ?? 'raw') === 'token' ? ($ben->aadhar_token ?? null) : ($ben->aadhar_no ?? null)) : null,
                'amount_rs' => $amountRs,
                'pension_id' => $ben->scheme_id,
                'unique_id' => $ben->created_by_dist_code . $ben->scheme_id . $ben->ben_id,
                'mobile_no' => $ben->mobile_no
            ];
        }

        foreach (array_chunk($sbiData, 500) as $chunk) {
            IfmsTransactionLotDetail::insert($chunk);
        }

        $lotMaster->update([
            'ben_count' => count($sbiData),
            'total_amount' => count($sbiData) * $amountRs,
        ]);
        SbiPaymentLotMasterAdditionalInfo::create([
                    'lot_no' => $lotMaster->lot_no,
                    'lot_year' => $lotMaster->lot_year,
                    'scheme_id' => $lotMaster->scheme_id,
                    'debit_reference' => $debitReference
        ]);
    }

    /**
     * Apply block/unblock constraints from LotControl model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param PaymentLotMaster $lotMaster
     * @return void
     */
    protected function applyLotControlFilters($query, PaymentLotMaster $lotMaster): void
    {
        $isRegular = $lotMaster->lot_type_id === '52301';
        $isArrear = $lotMaster->lot_type_id === '52302';
        
        $blockedColumn = $isRegular ? 'allow_regular_lot' : ($isArrear ? 'allow_arrear_lot' : null);

        if (!$blockedColumn) {
            return;
        }

        $lotControls = \App\Models\LotControl::where($blockedColumn, false)->get();
        
        if ($lotControls->isEmpty()) {
            return;
        }

        $blockedSchemes = $lotControls->where('blockable_type', \App\Models\Scheme::class)->pluck('blockable_id')->toArray();
        
        $blockedDistrictIds = $lotControls->where('blockable_type', \App\Models\District::class)->pluck('blockable_id')->toArray();
        $blockedDistCodes = !empty($blockedDistrictIds) ? \App\Models\District::whereIn('id', $blockedDistrictIds)->pluck('lgd_code')->toArray() : [];

        $blockedSubdivIds = $lotControls->where('blockable_type', \App\Models\Subdivision::class)->pluck('blockable_id')->toArray();
        
        $blockedBlockIds = $lotControls->where('blockable_type', \App\Models\Block::class)->pluck('blockable_id')->toArray();
        $blockedBlockCodes = !empty($blockedBlockIds) ? \App\Models\Block::whereIn('id', $blockedBlockIds)->pluck('lgd_code')->toArray() : [];
        
        $blockedMuniIds = $lotControls->where('blockable_type', \App\Models\Municipality::class)->pluck('blockable_id')->toArray();
        if (!empty($blockedSubdivIds)) {
            $subdivMunis = \App\Models\Municipality::whereIn('subdivision_id', $blockedSubdivIds)->pluck('id')->toArray();
            $blockedMuniIds = array_unique(array_merge($blockedMuniIds, $subdivMunis));
        }
        $blockedMuniCodes = !empty($blockedMuniIds) ? \App\Models\Municipality::whereIn('id', $blockedMuniIds)->pluck('lgd_code')->toArray() : [];
        
        $blockedPanchayatIds = $lotControls->where('blockable_type', \App\Models\Panchayat::class)->pluck('blockable_id')->toArray();
        $blockedGpCodes = !empty($blockedPanchayatIds) ? \App\Models\Panchayat::whereIn('id', $blockedPanchayatIds)->pluck('lgd_code')->toArray() : [];

        if (!empty($blockedSchemes)) {
            $query->whereNotIn('ben_payment_details.scheme_id', $blockedSchemes);
        }
        if (!empty($blockedDistCodes)) {
            $query->whereNotIn('ben_payment_details.dist_code', $blockedDistCodes);
        }
        if (!empty($blockedBlockCodes)) {
            $query->whereNotIn('ben_payment_details.block_code', $blockedBlockCodes);
        }
        if (!empty($blockedMuniCodes)) {
            $query->whereNotIn('ben_payment_details.municipality_code', $blockedMuniCodes);
        }
        if (!empty($blockedGpCodes)) {
            $query->whereNotIn('ben_payment_details.gp_code', $blockedGpCodes);
        }
    }

    /**
     * Preview the transaction lot records for the given criteria.
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
        $amountRs = 0;
        $setting = \App\Models\PaymentMainSetting::where('scheme_id', $schemeId)
            ->where('financial_year', $financialYear)
            ->first();

        if ($setting) {
            $monthField = strtolower($lotMonth);
            $monthData = $setting->$monthField;
            if (is_array($monthData) && isset($monthData['amount'])) {
                $amountRs = (float) $monthData['amount'];
            }
        }

        $benDetailsQuery = $this->getBaseBeneficiaryQuery($schemeId, $paymentType, $filters, $lotMonth, $financialYear, false);

        // Apply Lot Control Filters manually without a PaymentLotMaster model instance
        $isRegular = $lotTypeId === '52301';
        $isArrear = $lotTypeId === '52302';
        
        $blockedColumn = $isRegular ? 'allow_regular_lot' : ($isArrear ? 'allow_arrear_lot' : null);

        if ($blockedColumn) {
            $lotControls = \App\Models\LotControl::where($blockedColumn, false)->get();
            
            if (!$lotControls->isEmpty()) {
                $blockedSchemes = $lotControls->where('blockable_type', \App\Models\Scheme::class)->pluck('blockable_id')->toArray();
                
                $blockedDistrictIds = $lotControls->where('blockable_type', \App\Models\District::class)->pluck('blockable_id')->toArray();
                $blockedDistCodes = !empty($blockedDistrictIds) ? \App\Models\District::whereIn('id', $blockedDistrictIds)->pluck('lgd_code')->toArray() : [];

                $blockedSubdivIds = $lotControls->where('blockable_type', \App\Models\Subdivision::class)->pluck('blockable_id')->toArray();
                
                $blockedBlockIds = $lotControls->where('blockable_type', \App\Models\Block::class)->pluck('blockable_id')->toArray();
                $blockedBlockCodes = !empty($blockedBlockIds) ? \App\Models\Block::whereIn('id', $blockedBlockIds)->pluck('lgd_code')->toArray() : [];
                
                $blockedMuniIds = $lotControls->where('blockable_type', \App\Models\Municipality::class)->pluck('blockable_id')->toArray();
                if (!empty($blockedSubdivIds)) {
                    $subdivMunis = \App\Models\Municipality::whereIn('subdivision_id', $blockedSubdivIds)->pluck('id')->toArray();
                    $blockedMuniIds = array_unique(array_merge($blockedMuniIds, $subdivMunis));
                }
                $blockedMuniCodes = !empty($blockedMuniIds) ? \App\Models\Municipality::whereIn('id', $blockedMuniIds)->pluck('lgd_code')->toArray() : [];
                
                $blockedPanchayatIds = $lotControls->where('blockable_type', \App\Models\Panchayat::class)->pluck('blockable_id')->toArray();
                $blockedGpCodes = !empty($blockedPanchayatIds) ? \App\Models\Panchayat::whereIn('id', $blockedPanchayatIds)->pluck('lgd_code')->toArray() : [];

                if (!empty($blockedSchemes)) {
                    $benDetailsQuery->whereNotIn('ben_payment_details.scheme_id', $blockedSchemes);
                }
                if (!empty($blockedDistCodes)) {
                    $benDetailsQuery->whereNotIn('ben_payment_details.dist_code', $blockedDistCodes);
                }
                if (!empty($blockedBlockCodes)) {
                    $benDetailsQuery->whereNotIn('ben_payment_details.block_code', $blockedBlockCodes);
                }
                if (!empty($blockedMuniCodes)) {
                    $benDetailsQuery->whereNotIn('ben_payment_details.municipality_code', $blockedMuniCodes);
                }
                if (!empty($blockedGpCodes)) {
                    $benDetailsQuery->whereNotIn('ben_payment_details.gp_code', $blockedGpCodes);
                }
            }
        }

        $count = $benDetailsQuery->count();

        if (!empty($filters['limit']) && $count > $filters['limit']) {
            $count = (int) $filters['limit'];
        }

        return [
            'beneficiary_count' => $count,
            'total_amount' => $count * $amountRs,
        ];
    }

    /**
     * Build the base beneficiary query for generating or previewing a lot.
     * This makes the logic common for all target payment modes.
     *
     * @param int $schemeId
     * @param string $paymentType
     * @param array $filters
     * @param bool $includeSelects
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBaseBeneficiaryQuery(
        int $schemeId,
        string $paymentType,
        array $filters,
        string $lotMonth,
        string $financialYear,
        bool $includeSelects = true
    ) {
        $monthPrefix = strtolower(substr($lotMonth, 0, 3));

        $benDetailsQuery = \App\Models\BenPaymentDetail::where('ben_payment_details.scheme_id', $schemeId)
            ->where('ben_payment_details.is_eligible', true)
            ->where('ben_payment_details.is_rejected', false)
            ->join('ben_monthwise_payment_status', function ($join) use ($monthPrefix, $financialYear, $schemeId) {
                $join->on('ben_payment_details.ben_id', '=', 'ben_monthwise_payment_status.ben_id')
                     ->where('ben_monthwise_payment_status.financial_year', $financialYear)
                     ->where('ben_monthwise_payment_status.scheme_id', $schemeId)
                     ->where("ben_monthwise_payment_status.{$monthPrefix}_lot_status", 52101)
                     ->where("ben_monthwise_payment_status.{$monthPrefix}_is_eligible", true);
            });

        if (!empty($filters['district_id'])) {
            $benDetailsQuery->where('ben_payment_details.dist_code', $filters['district_id']);
        }
        if (!empty($filters['rural_urban_id'])) {
            $benDetailsQuery->where('ben_payment_details.rural_urban_id', $filters['rural_urban_id']);
        }
        if (!empty($filters['block_id'])) {
            $benDetailsQuery->where('ben_payment_details.block_code', $filters['block_id']);
        }
        if (!empty($filters['municipality_id'])) {
            $benDetailsQuery->where('ben_payment_details.municipality_code', $filters['municipality_id']);
        }
        if (!empty($filters['gp_id'])) {
            $benDetailsQuery->where('ben_payment_details.gp_code', $filters['gp_id']);
        }
        if (!empty($filters['ward_id'])) {
            $benDetailsQuery->where('ben_payment_details.ward_code', $filters['ward_id']);
        }

        if ($paymentType === '5001') {
            $benDetailsQuery->join('ben_payment_acc_details', 'ben_payment_details.ben_id', '=', 'ben_payment_acc_details.ben_id')
                ->where('ben_payment_acc_details.is_clean', true);
                
            if ($includeSelects) {
                $benDetailsQuery->select('ben_payment_details.ben_id', 'ben_payment_details.ben_name', 'ben_payment_details.created_by_dist_code', 'ben_payment_details.scheme_id', 'ben_payment_acc_details.last_accno as accno', 'ben_payment_acc_details.last_ifsc as ifsc');
            }
        } elseif ($paymentType === '5002') {
            $benDetailsQuery->join('ben_payment_abps_details', 'ben_payment_details.ben_id', '=', 'ben_payment_abps_details.ben_id')
                ->where('ben_payment_abps_details.is_clean', true);
                
            if ($includeSelects) {
                $benDetailsQuery->select('ben_payment_details.ben_id', 'ben_payment_details.ben_name', 'ben_payment_details.created_by_dist_code', 'ben_payment_details.scheme_id', 'ben_payment_abps_details.aadhar_no');
            }
        } else {
            throw new \InvalidArgumentException("Invalid payment type: {$paymentType}");
        }

        return $benDetailsQuery;
    }
}
