<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bankmaster extends Model
{
    protected $table = 'public.bankmasters';

    //  public function ifsccodes()
    // {
    //     return $this->hasMany(Ifsccodemaster::class, 'bankmaster_id', 'id');
    // }
}
