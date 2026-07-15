<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class IfmsValidationLotMasterAdditionalInfo extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_ifms';
    protected $table = 'ifms_validation_lot_master_additional_info';
    protected $guarded = [];

    //
}
