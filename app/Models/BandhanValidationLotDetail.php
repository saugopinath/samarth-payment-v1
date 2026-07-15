<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class BandhanValidationLotDetail extends Model implements Auditable
{
     use AuditableTrait;
    protected $connection = 'pgsql_bandhan';
    protected $table = 'bandhan_validation_lot_details';
    protected $guarded = [];

    //
}
