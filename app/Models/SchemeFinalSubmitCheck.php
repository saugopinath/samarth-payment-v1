<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeFinalSubmitCheck extends Model
{
    protected $table = 'scheme_final_submit_checks';

    protected $fillable = [
        'scheme_id',
        'is_final_submitted',
    ];

     public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}
