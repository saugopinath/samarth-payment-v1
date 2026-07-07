<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'name',
        'ref_code',
        'lgd_code',
        'state_ut'
    ];
}
