<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class BenMonthwisePaymentStatus extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'ben_monthwise_payment_status';
    protected $fillable = [
        'financial_year',
        'ben_id',
        'scheme_id',
        'present_amt',
        'present_count',
        'apr_lot_no', 'apr_lot_type', 'apr_lot_status', 'apr_is_eligible', 'apr_eligible_amount', 'apr_payment_amount',
        'may_lot_no', 'may_lot_type', 'may_lot_status', 'may_is_eligible', 'may_eligible_amount', 'may_payment_amount',
        'jun_lot_no', 'jun_lot_type', 'jun_lot_status', 'jun_is_eligible', 'jun_eligible_amount', 'jun_payment_amount',
        'jul_lot_no', 'jul_lot_type', 'jul_lot_status', 'jul_is_eligible', 'jul_eligible_amount', 'jul_payment_amount',
        'aug_lot_no', 'aug_lot_type', 'aug_lot_status', 'aug_is_eligible', 'aug_eligible_amount', 'aug_payment_amount',
        'sep_lot_no', 'sep_lot_type', 'sep_lot_status', 'sep_is_eligible', 'sep_eligible_amount', 'sep_payment_amount',
        'oct_lot_no', 'oct_lot_type', 'oct_lot_status', 'oct_is_eligible', 'oct_eligible_amount', 'oct_payment_amount',
        'nov_lot_no', 'nov_lot_type', 'nov_lot_status', 'nov_is_eligible', 'nov_eligible_amount', 'nov_payment_amount',
        'dec_lot_no', 'dec_lot_type', 'dec_lot_status', 'dec_is_eligible', 'dec_eligible_amount', 'dec_payment_amount',
        'jan_lot_no', 'jan_lot_type', 'jan_lot_status', 'jan_is_eligible', 'jan_eligible_amount', 'jan_payment_amount',
        'feb_lot_no', 'feb_lot_type', 'feb_lot_status', 'feb_is_eligible', 'feb_eligible_amount', 'feb_payment_amount',
        'mar_lot_no', 'mar_lot_type', 'mar_lot_status', 'mar_is_eligible', 'mar_eligible_amount', 'mar_payment_amount',
    ];
    //
}
