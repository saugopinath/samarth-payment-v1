<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class PaymentLotMaster extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'payment_lot_master';
    protected $primaryKey = 'lot_no';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    //
}
