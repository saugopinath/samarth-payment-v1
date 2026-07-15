<?php

namespace App\Observers;

use App\Models\BenPaymentDetail;
use App\Models\BenPaymentAccDetail;
use App\Models\BenPaymentAbpsDetail;
use App\Models\BenMonthwisePaymentStatus;

class BenPaymentDetailObserver
{
    /**
     * Handle the BenPaymentDetail "created" event.
     */
    public function created(BenPaymentDetail $benPaymentDetail): void
    {
         if($benPaymentDetail->last_accno && $benPaymentDetail->last_ifsc && $benPaymentDetail->npci_bank_code){
            BenPaymentAccDetail::create([
             'ben_id' => $benPaymentDetail->ben_id,
             'scheme_id' => $benPaymentDetail->scheme_id,
             'last_accno' => $benPaymentDetail->last_accno,
             'last_ifsc' => $benPaymentDetail->last_ifsc,
             'npci_bank_code' => $benPaymentDetail->npci_bank_code,
             'is_clean' => 1,
         ]);
        }

        if(trim($benPaymentDetail->aadhar_no) && strlen(trim($benPaymentDetail->aadhar_no)) == 12){
            BenPaymentAbpsDetail::create([
             'ben_id' => $benPaymentDetail->ben_id,
             'scheme_id' => $benPaymentDetail->scheme_id,
             'aadhar_no' => trim($benPaymentDetail->aadhar_no),
             'is_clean' => 1,
         ]);
        }

        $financialYear = \App\Helpers\FinancialYear::getCurrentFinancialYear()->get();

        $amounts = \App\Models\SchemePaymentAmount::where('scheme_id', $benPaymentDetail->scheme_id)
            ->where('financial_year', $financialYear)
            ->first();

        BenMonthwisePaymentStatus::create([
             'ben_id' => $benPaymentDetail->ben_id,
             'scheme_id' => $benPaymentDetail->scheme_id,
             'financial_year' => $financialYear,
             'apr_eligible_amount' => $amounts->april_amount ?? 0,
             'may_eligible_amount' => $amounts->may_amount ?? 0,
             'jun_eligible_amount' => $amounts->june_amount ?? 0,
             'jul_eligible_amount' => $amounts->july_amount ?? 0,
             'aug_eligible_amount' => $amounts->august_amount ?? 0,
             'sep_eligible_amount' => $amounts->september_amount ?? 0,
             'oct_eligible_amount' => $amounts->october_amount ?? 0,
             'nov_eligible_amount' => $amounts->november_amount ?? 0,
             'dec_eligible_amount' => $amounts->december_amount ?? 0,
             'jan_eligible_amount' => $amounts->january_amount ?? 0,
             'feb_eligible_amount' => $amounts->february_amount ?? 0,
             'mar_eligible_amount' => $amounts->march_amount ?? 0,
        ]);
    }

    /**
     * Handle the BenPaymentDetail "updated" event.
     */
    public function updated(BenPaymentDetail $benPaymentDetail): void
    {
       
    }

    /**
     * Handle the BenPaymentDetail "deleted" event.
     */
    public function deleted(BenPaymentDetail $benPaymentDetail): void
    {
        //
    }

    /**
     * Handle the BenPaymentDetail "restored" event.
     */
    public function restored(BenPaymentDetail $benPaymentDetail): void
    {
        //
    }

    /**
     * Handle the BenPaymentDetail "force deleted" event.
     */
    public function forceDeleted(BenPaymentDetail $benPaymentDetail): void
    {
        //
    }
}
