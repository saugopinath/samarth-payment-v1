<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class IfmsPaymentLotMasterAdditionalInfo extends Model implements Auditable
{
    use AuditableTrait;

    protected $connection = 'pgsql_payment';
    protected $table = 'ifms.payment_lot_master_additional_info';
    protected $fillable = [
        'lot_no',
        'scheme_id',
        'lot_year',
        'dotdone_status',
        'ack_status',
        'ref_no',
        'received_ifms_error_file',
        'ifms_wrongdata_count',
        'utr_no',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
