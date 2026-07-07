<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeTabLayout extends Model
{
    protected $guarded = [];
    protected $casts = [
        'layout_json' => 'array',
    ];
}
