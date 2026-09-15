<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IfmsPaymentSetting extends Model
{
    protected $fillable = [
        'scheme_id',
        'client_id',
        'client_secret',
        'party_code',
        'ddo_code',
        'hoa_id',
        'hoa_user',
        'treasury_code',
    ];

    protected $casts = [
        'client_secret' => 'encrypted',
    ];
}
