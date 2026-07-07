<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SchemeCapacity extends Model
{
    protected $table = 'scheme_capacities';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Polymorphic relation
     * Scheme / District / Block
     */
    public function modelable(): MorphTo
    {
        return $this->morphTo(
            name: 'modelable',
            type: 'model_type',
            id: 'model_id'
        );
    }
    // /**
    //  * Remaining capacity helper
    //  */
    // public function getRemainingCapacityAttribute(): int
    // {
    //     return max(0, $this->total_capacity - $this->used_capacity);
    // }

    // /**
    //  * Scope: Active
    //  */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
