<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FailedPaymentDetails extends Model implements Auditable
{
    use \App\Traits\ZoneAwareModel;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'failed_payment_details';

     protected $guarded = [];
}

