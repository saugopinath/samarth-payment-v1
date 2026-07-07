<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeTabFieldTemp extends Model
{
    protected $fillable = [
        'scheme_id',
        'tab_code',
        'field_ids',
    ];
    protected $casts = [
        'field_ids' => 'array',
    ];
}
