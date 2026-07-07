<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicWorkflowLabel extends BaseAuditableModel
{
    protected $table = 'dynamic_workflow_labels';

    protected $fillable = [
        'scheme_id',
        'module_id',
        'op_type_id',
        'label_name',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    public function module()
    {
        return $this->belongsTo(DynamicWorkflowSchemeModule::class, 'module_id');
    }

    public static function getOpTypeId($labelId)
    {
        return self::where('id', $labelId)->value('op_type_id');
    }
}
