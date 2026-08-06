<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class FailedPaymentDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'failed_payment_details';
    protected $fillable = [
        'lot_no',
        'ben_id',
        'scheme_id',
        'validation_type',
        'failed_source',
        'failed_type',
        'status_code',
        'remarks',
        'name_status',
        'name_status_code',
        'name_response',
        'matching_score',
    ];
    //
}
