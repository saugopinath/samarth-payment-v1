<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenPaymentDetailsJB extends Model
{
    use \App\Traits\ZoneAwareModel;

    protected $connection = 'pgsql_jbpayread';
    protected $table = 'payment.ben_payment_details';
    protected $primaryKey = 'ben_id';

    public $timestamps = false; // set true if table has timestamps

    protected $fillable = [
        "dist_code",
        "ben_id",
        "scheme_id",
        "last_accno",
        "last_ifsc",
        "ben_status",
        "acc_validated",
        "ben_name",
        "local_body_code",
        "rural_urban_id",
        "block_ulb_code",
        "gp_ward_code",
        "created_at",
        "updated_at",
        "deleted_at",
        "caste",
        "gender",
        "mobile_no",
        "npci_bank_code",
        "applied_at",
        "approval_at",
        "rejected_at",
        "is_eligible",
        "pay_validated",
        "is_rejected",
        "dup_bank",
        "total_amt",
        "total_count",
        "payment_process",
        "payment_start_at",
        "legacy_validation",
        "legacy_validated",
        "lb_imported",
    ];

}

