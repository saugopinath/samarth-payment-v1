<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicWorkflowRequest extends BaseAuditableModel
{
    protected $table = 'dynamic_workflow_requests';
    protected $fillable = [
        'module_id',
        'ref_id',
        'scheme_id',
        'current_rank',
        'current_step_id',
        'old_data',
        'new_data',
        'changed_fields',
        'created_by'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'changed_fields' => 'array'
    ];

    public function module()
    {
        return $this->belongsTo(DynamicWorkflowSchemeModule::class, 'module_id');
    }

    public function step()
    {
        return $this->belongsTo(
            workflowstepRolemapping::class,
            'current_step_id',     // FK in request table
            'workflow_step_id'     // column in mapping table
        );
    }

    public function beneficiary()
    {
        return $this->belongsTo(BeneficiaryPersonalDetail::class, 'ref_id', 'application_id');
    }
}
