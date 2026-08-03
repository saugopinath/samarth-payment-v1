<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class SbiPaymentLotMasterAdditionalInfo extends Model implements Auditable
{
    use AuditableTrait;

    protected $connection = 'pgsql_payment';
    protected $table = 'sbi.payment_lot_master_additional_info';
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
