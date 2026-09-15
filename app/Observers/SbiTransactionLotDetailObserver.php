<?php

namespace App\Observers;

use App\Models\SbiTransactionLotDetail;

class SbiTransactionLotDetailObserver
{
    /**
     * Handle the SbiTransactionLotDetail "created" event.
     */
    public function created(SbiTransactionLotDetail $sbiTransactionLotDetail): void
    {
        $lotMaster = \App\Models\PaymentLotMaster::where('lot_no', $sbiTransactionLotDetail->lot_no)->first();
        if ($lotMaster && $lotMaster->lot_month) {
            $monthField = strtolower(substr($lotMaster->lot_month, 0, 3)) . '_lot_status';
            
            \App\Models\BenMonthwisePaymentStatus::where('ben_id', $sbiTransactionLotDetail->ben_id)
                ->where('financial_year', $sbiTransactionLotDetail->lot_year)
                ->where('scheme_id', $sbiTransactionLotDetail->scheme_id)
                ->update([$monthField => 'Generated']);
        }
    }

    /**
     * Handle the SbiTransactionLotDetail "updated" event.
     */
    public function updated(SbiTransactionLotDetail $sbiTransactionLotDetail): void
    {
        if ($sbiTransactionLotDetail->wasChanged('status_code') && $sbiTransactionLotDetail->isDirty('status_code')) {

            if($sbiTransactionLotDetail->status_code == '52102'){
                $statusCode = Codemaster::where('code',52103)->first()->code;
            }else if($sbiTransactionLotDetail->status_code == '52103'){
                $statusCode = Codemaster::where('code',52104)->first()->code;
            }else if($sbiTransactionLotDetail->status_code == 'S00'){
                $statusCode = Codemaster::where('code',52106)->first()->code;
            }
            else if($sbiTransactionLotDetail->status_code == 'S00'){
                $statusCode = Codemaster::where('code',52106)->first()->code;
            }
            
            if($statusCode){
            $lotMaster = \App\Models\PaymentLotMaster::where('lot_no', $sbiTransactionLotDetail->lot_no)->first();
            if ($lotMaster && $lotMaster->lot_month) {
                $monthField = strtolower(substr($lotMaster->lot_month, 0, 3)) . '_lot_status';
                
                \App\Models\BenMonthwisePaymentStatus::where('ben_id', $sbiTransactionLotDetail->ben_id)
                    ->where('financial_year', $sbiTransactionLotDetail->lot_year)
                    ->where('scheme_id', $sbiTransactionLotDetail->scheme_id)
                    ->update([$monthField => $statusCode]);
            }
        }
       }
    }

    /**
     * Handle the SbiTransactionLotDetail "deleted" event.
     */
    public function deleted(SbiTransactionLotDetail $sbiTransactionLotDetail): void
    {
        //
    }

    /**
     * Handle the SbiTransactionLotDetail "restored" event.
     */
    public function restored(SbiTransactionLotDetail $sbiTransactionLotDetail): void
    {
        //
    }

    /**
     * Handle the SbiTransactionLotDetail "force deleted" event.
     */
    public function forceDeleted(SbiTransactionLotDetail $sbiTransactionLotDetail): void
    {
        //
    }
}
