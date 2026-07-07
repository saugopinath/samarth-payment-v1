<?php

namespace App\Models;

class SchemeAttachedDocMappings extends BaseAuditableModel
{
    protected $table = 'scheme_attached_doc_mappings';

    protected $fillable = [
        'scheme_id',
        'doc_type_id',
        'tab_code',
        'is_required',
        'max_file_size',
        'extension_type',
        'field_position',
        'is_active',
    ];


    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];
    public function codemaster()
    {
        return $this->belongsTo(Codemaster::class, 'doc_type_id');
    }
    public function docType()
    {
        return $this->belongsTo(Codemaster::class, 'doc_type_id');
    }
}
