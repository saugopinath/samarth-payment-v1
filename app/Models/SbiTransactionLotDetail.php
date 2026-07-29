<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Observers\SbiTransactionLotDetailObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([SbiTransactionLotDetailObserver::class])]
class SbiTransactionLotDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_sbi';
    protected $table = 'sbi.transaction_lot_details';
    protected $guarded = [];

    //
}
