<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Observers\PaymentLotMasterObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([PaymentLotMasterObserver::class])]
class PaymentLotMaster extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'payment_lot_master';
    protected $primaryKey = 'lot_no';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $guarded = [];
    protected $fillable = [
		'lot_month',
		'lot_year',
		'scheme_id',
		'payment_mode',
		'lot_type_id',
        'ben_count',
        'total_amount',
        'success_count',
        'failed_count',
        'success_amount',
        'failed_amount',
		'cur_status'
	];

    public function sbiPaymentLotMasterAdditionalInfo()
    {
        return $this->hasOne(SbiPaymentLotMasterAdditionalInfo::class, 'lot_no', 'lot_no');
    }
}
