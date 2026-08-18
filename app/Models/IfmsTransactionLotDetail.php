<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class IfmsTransactionLotDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_ifms';
    protected $table = 'ifms.transaction_lot_details';
    protected $fillable = [
        'lot_no',
        'lot_year',
        'scheme_id',
        'ben_id',
        'accno',
        'ifsc',
        'amount_rs',
        'status',
        'reason',
        'utr_no',
        'processed_flag',
        'push_to_ifms_status',
        'dotdone_status',
        'ack_status',
        'wrongdata_status',
        'drn',
        'voucher_no',
        'voucher_date',
        'token_no',
        'token_date',
        'ifms_wrongdata_count',
        'rbi_failed_count',
        'rbi_success_count'
    ];
    //
}
