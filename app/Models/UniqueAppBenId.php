<?php

namespace App\Models;

class UniqueAppBenId extends BaseAuditableModel
{
    protected $table = 'pension.unique_app_ben_ids';
    protected $primaryKey = 'application_id';
    protected $fillable = ['application_id', 'beneficiary_id', 'scheme_id'];
}
