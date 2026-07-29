<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Observers\PaymentLotMasterObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([PaymentLotMasterObserver::class])]
class PaymentLotMaster extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'payment_lot_master';
    protected $primaryKey = 'lot_no';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $guarded = [];

    //
}
