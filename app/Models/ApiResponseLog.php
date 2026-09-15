<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiResponseLog extends Model
{
       protected $fillable = [
        'api_name',
        'request_data',
        'http_status',
        'response_data',
        'success',
        'error_message',
        'ip_address',
        'user_agent',
    ];
}
