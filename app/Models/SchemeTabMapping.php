<?php

namespace App\Models;

class SchemeTabMapping extends BaseAuditableModel
{
    protected $guarded = [];
    public function masterTab()
    {
        return $this->belongsTo(
            MasterTab::class,
            'tab_code',   // FK in scheme_tab_mappings
            'tab_code'    // PK in master_tabs
        );
    }
   

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
    public function showValidationButton(): bool
    {
        $hiddenTabs = [104];
        return !in_array($this->tab_code, $hiddenTabs, true);
    }
}
