<?php

namespace App\Observers;

use App\Models\PaymentLotMaster;

class PaymentLotMasterObserver
{
    /**
     * Handle the PaymentLotMaster "created" event.
     */
    public function created(PaymentLotMaster $paymentLotMaster): void
    {
        //
    }

    /**
     * Handle the PaymentLotMaster "updated" event.
     */
    public function updated(PaymentLotMaster $paymentLotMaster): void
    {
        //
    }

    /**
     * Handle the PaymentLotMaster "deleted" event.
     */
    public function deleted(PaymentLotMaster $paymentLotMaster): void
    {
        //
    }

    /**
     * Handle the PaymentLotMaster "restored" event.
     */
    public function restored(PaymentLotMaster $paymentLotMaster): void
    {
        //
    }

    /**
     * Handle the PaymentLotMaster "force deleted" event.
     */
    public function forceDeleted(PaymentLotMaster $paymentLotMaster): void
    {
        //
    }
}
