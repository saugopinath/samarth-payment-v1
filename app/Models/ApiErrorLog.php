<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiErrorLog extends Model
{
    protected $fillable = [
        'api_name',
        'request_data',
        'error_message',
        'stack_trace',
        'ip_address',
        'user_agent',
    ];
}
