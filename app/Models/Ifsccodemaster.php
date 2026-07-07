<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ifsccodemaster extends Model
{
    protected $table = 'public.ifsccodemasters';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    public function bankMaster()
    {
        return $this->belongsTo(BankMaster::class, 'bankmaster_id', 'id');
    }
    public function bank()
    {
        return $this->belongsTo(BankMaster::class, 'bankmaster_id');
    }
}
