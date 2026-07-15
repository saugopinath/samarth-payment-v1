<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Observers\BenPaymentDetailObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
#[ObservedBy([BenPaymentDetailObserver::class])]

class BenPaymentDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'ben_payment_details';
    protected $primaryKey = ['ben_id', 'scheme_id'];
    public $incrementing = false;
    protected $fillable = [
        'ben_id',
        'scheme_id',
        'ben_name',
        'last_accno',
        'last_ifsc',
        'npci_bank_code',
        'aadhar_no',
        'last_acc_validated',
        'last_acc_validated_reason',
        'last_aadhar_validated',
        'last_aadhar_validated_reason',
        'caste',
        'gender',
        'mobile_no',
        'created_by_dist_code',
        'created_by_sdo_code',
        'created_by_block_code',
        'dist_code',
        'rural_urban_id',
        'block_code',
        'municipality_code',
        'gp_code',
        'ward_code',
        'applied_at',
        'approval_at',
        'rejected_at',
        'is_eligible',
        'non_eligible_reason',
        'is_rejected',
        'rejection_cause'
    ];
    //
}
