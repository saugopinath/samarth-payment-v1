<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BeneficiaryEnclosure extends BaseAuditableModel
{
    use \App\Traits\ZoneAwareModel;
    use HasFactory;
    protected $table = 'pension.beneficiary_documents';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_clean' => 'boolean',
    ];

    /**
     * Relation: Personal Details
     */
    public function personal()
    {
        return $this->belongsTo(BeneficiaryPersonalDetail::class, 'application_id', 'application_id');
    }

    /**
     * Relation: Codemaster (Document Type)
     */
    public function documentType()
    {
        return $this->belongsTo(Codemaster::class, 'document_type', 'id');
    }

}

