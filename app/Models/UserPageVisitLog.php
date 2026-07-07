<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPageVisitLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'request_payload' => 'json',
        'response_payload' => 'json',
        'visit_time' => 'datetime',
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
