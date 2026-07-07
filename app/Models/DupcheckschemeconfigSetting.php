<?php

namespace App\Models;

class DupcheckschemeconfigSetting extends BaseAuditableModel
{
    protected $guarded = ['id'];
    protected $casts = [
        'scheme_lists' => 'array',
    ];    
}
