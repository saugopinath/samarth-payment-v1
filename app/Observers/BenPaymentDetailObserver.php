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

        $setting = \App\Models\PaymentMainSetting::where('scheme_id', $benPaymentDetail->scheme_id)
            ->where('financial_year', $financialYear)
            ->first();

        BenMonthwisePaymentStatus::create([
             'ben_id' => $benPaymentDetail->ben_id,
             'scheme_id' => $benPaymentDetail->scheme_id,
             'financial_year' => $financialYear,
             'apr_eligible_amount' => ($setting && is_array($setting->apr) && isset($setting->apr['amount'])) ? (float) $setting->apr['amount'] : 0,
             'may_eligible_amount' => ($setting && is_array($setting->may) && isset($setting->may['amount'])) ? (float) $setting->may['amount'] : 0,
             'jun_eligible_amount' => ($setting && is_array($setting->jun) && isset($setting->jun['amount'])) ? (float) $setting->jun['amount'] : 0,
             'jul_eligible_amount' => ($setting && is_array($setting->jul) && isset($setting->jul['amount'])) ? (float) $setting->jul['amount'] : 0,
             'aug_eligible_amount' => ($setting && is_array($setting->aug) && isset($setting->aug['amount'])) ? (float) $setting->aug['amount'] : 0,
             'sep_eligible_amount' => ($setting && is_array($setting->sep) && isset($setting->sep['amount'])) ? (float) $setting->sep['amount'] : 0,
             'oct_eligible_amount' => ($setting && is_array($setting->oct) && isset($setting->oct['amount'])) ? (float) $setting->oct['amount'] : 0,
             'nov_eligible_amount' => ($setting && is_array($setting->nov) && isset($setting->nov['amount'])) ? (float) $setting->nov['amount'] : 0,
             'dec_eligible_amount' => ($setting && is_array($setting->dec) && isset($setting->dec['amount'])) ? (float) $setting->dec['amount'] : 0,
             'jan_eligible_amount' => ($setting && is_array($setting->jan) && isset($setting->jan['amount'])) ? (float) $setting->jan['amount'] : 0,
             'feb_eligible_amount' => ($setting && is_array($setting->feb) && isset($setting->feb['amount'])) ? (float) $setting->feb['amount'] : 0,
             'mar_eligible_amount' => ($setting && is_array($setting->mar) && isset($setting->mar['amount'])) ? (float) $setting->mar['amount'] : 0,
        ]);
    }

    /**
     * Handle the BenPaymentDetail "updated" event.
     */
    public function updated(BenPaymentDetail $benPaymentDetail): void
    {
        if ($benPaymentDetail->isDirty(['last_accno', 'last_ifsc', 'npci_bank_code'])) {
            if ($benPaymentDetail->last_accno && $benPaymentDetail->last_ifsc && $benPaymentDetail->npci_bank_code) {
                BenPaymentAccDetail::updateOrCreate(
                    ['ben_id' => $benPaymentDetail->ben_id,'scheme_id' => $benPaymentDetail->scheme_id],
                    [
                        'last_accno' => $benPaymentDetail->last_accno,
                        'last_ifsc' => $benPaymentDetail->last_ifsc,
                        'npci_bank_code' => $benPaymentDetail->npci_bank_code,
                        'is_clean' => 1,
                    ]
                );
            } else {
                BenPaymentAccDetail::where('ben_id', $benPaymentDetail->ben_id)->where('scheme_id', $benPaymentDetail->scheme_id)->delete();
            }
        }

        if ($benPaymentDetail->isDirty(['aadhar_no'])) {
            if (trim($benPaymentDetail->aadhar_no) && strlen(trim($benPaymentDetail->aadhar_no)) == 12) {
                BenPaymentAbpsDetail::updateOrCreate(
                    ['ben_id' => $benPaymentDetail->ben_id,'scheme_id' => $benPaymentDetail->scheme_id],
                    [
                        'aadhar_no' => trim($benPaymentDetail->aadhar_no),
                        'is_clean' => 1,
                    ]
                );
            } else {
                BenPaymentAbpsDetail::where('ben_id', $benPaymentDetail->ben_id)->delete();
            }
        }


    }

    /**
     * Handle the BenPaymentDetail "deleted" event.
     */
    public function deleted(BenPaymentDetail $benPaymentDetail): void
    {
        $financialYear = \App\Helpers\FinancialYear::getCurrentFinancialYear()->get();
        BenPaymentAccDetail::where('ben_id', $benPaymentDetail->ben_id)->delete();
        BenPaymentAbpsDetail::where('ben_id', $benPaymentDetail->ben_id)->delete();
        BenMonthwisePaymentStatus::where('financial_year', $financialYear)->where('ben_id', $benPaymentDetail->ben_id)->delete();
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
