<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class BenPaymentAbpsDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'ben_payment_abps_details';
    protected $fillable = [
        'ben_id',
        'scheme_id',
        'aadhar_no',
        'is_clean'
    ];

    //
}
