<?php

namespace App\Models;


class BeneficiaryBankDetail extends BaseAuditableModel
{
    use \App\Traits\ZoneAwareModel;
    protected $table = "pension.beneficiary_banks";
    protected $primaryKey = 'application_id';
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = [
        'other_details' => 'array',
    ];
    public function personal()
    {
        return $this->belongsTo(BeneficiaryPersonalDetail::class, 'application_id', 'application_id');
    }

    public function bankname()
    {
        $ifsc = $this->ifscbranch;
        $accno = $this->bank_account_number;
        if ($ifsc && $ifsc->bank) {
            return [
                'bank_name' => $ifsc->bank->name,
                'branch_name' => $ifsc->branch,
                'ifsc_code' => $ifsc->code,
                'accno' => $accno,
            ];
        }
    }
}

