<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiStepLog extends Model
{
    protected $fillable = [
        'user_id',
        'step_name',
        'lot_no',
        'drn_no',
        'is_success',
        'message',
        'ip_address',
        'user_agent',
    ];
}
