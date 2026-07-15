<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class ValidationLotMaster extends Model implements Auditable
{
	 use AuditableTrait;
    protected $connection = 'pgsql_payment';
    protected $table = 'validation_lot_master';
    protected $guarded = [];

    //
}
