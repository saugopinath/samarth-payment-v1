<?php

namespace App\Models;

class RoleOfficeTypeMapping extends BaseAuditableModel
{
    protected $table = 'role_office_type_mappings';
    protected $fillable = [
        'office_type_id',
        'role_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function officeType()
    {
        return $this->belongsTo(Codemaster::class, 'office_type_id', 'code');
    }
}
