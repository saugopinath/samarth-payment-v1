<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class BenPaymentAccDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'ben_payment_acc_details';
    protected $fillable = [
        'ben_id',
        'scheme_id',
        'last_accno',
        'last_ifsc',
        'npci_bank_code',
        'is_clean'
    ];

    //
}
