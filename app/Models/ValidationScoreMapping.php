<?php

namespace App\Models;


class ValidationScoreMapping extends BaseAuditableModel
{
    protected $fillable = [
        'permission_id',
        'min_score',
        'max_score',
    ];
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
