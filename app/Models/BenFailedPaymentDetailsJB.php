<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenFailedPaymentDetailsJB extends Model
{
    use \App\Traits\ZoneAwareModel;
    protected $connection = 'pgsql_jbpayread';
    protected $table = 'payment.failed_payment_details';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        "id",
        "dist_code",
        "ben_id",
        "scheme_id",
        "lot_no",
        "accno",
        "ifsc",
        "pmt_mode",
        "failed_type",
        "edited_status",
        "lot_month",
        "status_code",
        "remarks",
        "fin_year",
        "created_at",
        "updated_at",
        "deleted_at",
        "lot_type",
        "av_status_code",
        "av_name_response",
        "input_file_name",
        "failed_process_type",
        "updated_details",
        "local_body_code",
        "ben_name",
        "failed_marked",
        "matching_score",
        "if_previous_approve",
        "approve_edited_status",
        "visiting_time",
        "visiting_mark_date",
        "failed_payment_details",
        "process_complete",
        "tagging_time"
    ];

}


