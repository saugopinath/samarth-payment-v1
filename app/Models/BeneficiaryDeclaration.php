<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class BeneficiaryDeclaration extends Model implements Auditable
{
    use \App\Traits\ZoneAwareModel;
    use \OwenIt\Auditing\Auditable;
    protected $guarded = [];
    protected $table = 'lb_scheme.beneficiary_declarations';
}

