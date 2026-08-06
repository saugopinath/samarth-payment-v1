<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SbiPaymentSetting extends Model
{
    protected $fillable = [
        'scheme_id',
        'npci_user_id',
        'npci_user_name',
        'bank_account_no',
        'ifsc_code',
        'email',
    ];

    protected $casts = [
        'npci_user_id' => 'encrypted',
        'npci_user_name' => 'encrypted',
        'bank_account_no' => 'encrypted',
    ];
}
