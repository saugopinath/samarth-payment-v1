<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeTabFormField extends Model
{
    protected $table = 'scheme_tab_form_fields';

    protected $guarded = [];
    protected $casts = [
        'options' => 'array',
        'dependent_on_values' => 'array',
        'is_common' => 'boolean',
        'is_multiple' => 'boolean',
        'is_active' => 'boolean',
    ];
    public function tabMaster()
    {
        return $this->belongsTo(MasterTab::class, 'tab_code', 'tab_code');
    }
    
}
