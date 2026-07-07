<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnapurnaYojanaApplication extends Model
{
    use \App\Traits\ZoneAwareModel;
    protected $connection = 'pgsql_apy_uat';
    protected $table = 'annapurna_yojana_applications';

    protected $guarded = [];

    protected $casts = [
        'form_data' => 'array',
    ];
}

