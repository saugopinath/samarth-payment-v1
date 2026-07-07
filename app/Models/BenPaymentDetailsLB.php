<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenPaymentDetailsLB extends Model
{
    use \App\Traits\ZoneAwareModel;

    protected $connection = 'pgsql_lbpayread';
    protected $table = 'payment.ben_payment_details';
    protected $primaryKey = 'ben_id';

    public $timestamps = false; // set true if table has timestamps

    protected $fillable = [
        "dist_code",
        "ben_id",
        "scheme_id",
        "application_id",
        "ben_status",
        "acc_validated",
        "is_eligible",
        "dup_bank",
        "ss_card_no",
        "mobile_no",
        "ben_name",
        "last_accno",
        "last_ifsc",
        "caste",
        "local_body_code",
        "rural_urban_id",
        "block_ulb_code",
        "gp_ward_code",
        "payment_process",
        "total_amt",
        "total_count",
        "start_yymm",
        "end_yymm",
        "created_at",
        "updated_at",
        "faulty_status",
        "faulty_to_main_date",
        "is_rejected",
        "rejected_date",
        "is_caste_changed",
        "effective_yymm",
        "ds_phase",
        "legacy_validated",
        "name_validated",
        "name_validated_modified",
        "arrear_caste_month",
        "payment_report",
        "payment_update_status",
        "fy_is_migrated",
        "fy_migration_type",
        "jnmp_marked",
        "openning_due_amt",
        "openning_due_count",
        "arrear_lot_status",
        "arrear_lot_type"
    ];

}
