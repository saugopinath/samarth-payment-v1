<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class SbiValidationLotDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_sbi';
    protected $table = 'sbi_validation_lot_details';
    protected $guarded = [];

    //
}
