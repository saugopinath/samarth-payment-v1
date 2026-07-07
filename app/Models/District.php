<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'name',
        'ref_code',
        'lgd_code',
        'short_name',
        'state_id',
    ];
    public const BENEFICIARY_LOCATION_COLUMN = 'created_by_dist_code';
    public function State(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    // public function municipalities()
    // {
    //     return $this->hasMany(Municipality::class);
    // }

    public function subdivisions()
    {
        return $this->hasMany(Subdivision::class);
    }

    public function municipalities()
    {
        return $this->hasManyThrough(
            Municipality::class,
            Subdivision::class,
            'district_id',
            'subdivision_id',
            'id',
            'id'
        );
    }
    public function capacities()
    {
        return $this->morphMany(SchemeCapacity::class, 'modelable', 'model_type', 'model_id');
    }
}
