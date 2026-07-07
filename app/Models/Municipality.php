<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Municipality extends Model
{
    protected $table = 'municipalities';
    protected $fillable = [
        'name',
        'ref_code',
        'lgd_code',
        'district_id',
        'subdivision_id',
        'state_id',
    ];


    public function Subdivision(): BelongsTo
    {
        return $this->belongsTo(Subdivision::class);
    }


    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}
