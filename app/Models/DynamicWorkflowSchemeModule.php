<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicWorkflowSchemeModule extends Model
{
    use HasFactory;

    protected $table = 'dynamic_workflow_scheme_modules';

    protected $fillable = [
        'scheme_id',
        'module_id',
        'main_module_code',
        'step_count',
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function module()
    {
        return $this->belongsTo(DynamicWorkflowModule::class, 'module_id');
    }

    public function steps()
    {
        return $this->hasMany(DynamicWorkflowLabel::class, 'module_id'); // Notice module_id now points to this table's ID per migration
    }
}
