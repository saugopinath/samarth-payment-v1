<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scheme extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'short_name',
        'description',
        'department_id',
        'is_active',
        'min_age',
        'max_age',
        'base_amount',
        'allow_entry',
        'allow_verification',
        'allow_approval'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_entry' => 'boolean',
        'allow_verification' => 'boolean',
        'allow_approval' => 'boolean',
    ];


    public function Department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function workflowSteps()
    {
        return $this->hasMany(WorkflowStep::class, 'scheme_id');
    }
    public function capacities()
    {
        return $this->morphMany(SchemeCapacity::class, 'modelable', 'model_type', 'model_id');
    }
    public function schemeFinalSubmitChecks()
    {
        return $this->hasMany(SchemeFinalSubmitCheck::class);
    }

    public function duplicateCheckSettings()
    {
        return $this->hasMany(DupcheckschemeconfigSetting::class, 'scheme_id');
    }
}
