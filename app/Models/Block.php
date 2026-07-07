<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    protected $fillable = [
        'name',
        'ref_code',
        'lgd_code',
        'district_id',
        'state_id',
    ];
public const BENEFICIARY_LOCATION_COLUMN = 'created_by_local_body_code';
    public function District(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function panchayats()
    {
        return $this->hasMany(Panchayat::class);
    }
    public function capacities()
    {
        return $this->morphMany(SchemeCapacity::class, 'modelable', 'model_type', 'model_id');
    }
}
