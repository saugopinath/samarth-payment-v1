<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'id';

    protected $table = 'public.notifications';
    
    protected $casts = [
        'notified_at' => 'datetime',
    ];
}
