<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class IfmsV3TransactionLotDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_ifms_v3';
    protected $table = 'ifms_v3_transaction_lot_details';
    protected $guarded = [];

    //
}
