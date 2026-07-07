<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DynamicWorkflowModule extends BaseAuditableModel
{
    protected $table = 'dynamic_workflow_modules';
    
    protected $fillable = [
        'module_code',
        'module_name',
        'allowed_fields',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'allowed_fields' => 'array',
        'is_active' => 'boolean'
    ];

    public function schemeModules()
    {
        return $this->hasMany(DynamicWorkflowSchemeModule::class, 'module_id');
    }
}
