<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Observers\SbiTransactionLotDetailObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

//#[ObservedBy([SbiTransactionLotDetailObserver::class])]
class SbiTransactionLotDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_sbi';
    protected $table = 'sbi.transaction_lot_details';
    protected $fillable = [
        'lot_no',
        'lot_year',
        'scheme_id',
        'ben_id',
        'ben_name',
        'ifsc',
        'accno',
        'aadhar_no',
        'amount_rs',
        'status_code',
        'remarks',
        'debit_reference',
        'credit_reference',
        'npci_user_id',
        'npci_user_name',
        'agency_cr_ref',
        'credit_payment_reference'
    ];
    //
}
