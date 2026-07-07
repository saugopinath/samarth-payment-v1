<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriticalChangeMaster extends Model
{
    protected $table = 'critical_change_masters';

    protected $fillable = [
        'name',
        'short_name',
        'code',
        'is_active',
    ];
    public static function getIdByCode($code)
    {
        return self::where('code', $code)->value('id');
    }
}
