<?php

namespace App\Models;


class SchemeTabBasefield extends BaseAuditableModel
{ 
    protected $guarded = [];

    protected $casts = [
        'options'     => 'array',
        'dependent_on_values' => 'array',
        'is_common'   => 'boolean',
        'is_multiple' => 'boolean',
        'is_active'   => 'boolean',
    ];
}
