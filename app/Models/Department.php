<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Department extends Model
{
     protected $fillable = [
        'name',
        'short_name',
        'state_id',
    ];
    public function State(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
