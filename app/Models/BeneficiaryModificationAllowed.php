<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BeneficiaryModificationAllowed extends Model implements Auditable
{
    use \App\Traits\ZoneAwareModel;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'lb_scheme.beneficiary_modification_alloweds';
    protected $guarded = ['id'];
    protected $casts = [
        'allowed_fields' => 'array',
        'old_data' => 'array',
        'new_data' => 'array',
    ];
    public function beneficiaryCommonList()
    {
        return $this->hasOne(BeneficiaryCommonList::class, 'sourceable_id', 'application_id');
    }
    public function getAllowedFieldNames(): array
    {
        return collect($this->allowed_fields)
            ->pluck('short_name')
            ->filter()
            ->values()
            ->toArray();
    }
}

