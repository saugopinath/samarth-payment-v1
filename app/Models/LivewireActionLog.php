<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class LivewireActionLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
