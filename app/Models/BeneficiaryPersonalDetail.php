<?php

namespace App\Models;


class BeneficiaryPersonalDetail extends BaseAuditableModel
{
    use \App\Traits\ZoneAwareModel;

    protected $guarded = [];

    protected $table = 'pension.beneficiary_personals';

    protected $primaryKey = 'application_id';

    public $incrementing = false;

    protected $casts = [
        'other_details' => 'array',
    ];

    public function contact()
    {
        return $this->hasOne(BeneficiaryContactDetail::class, 'application_id', 'application_id');
    }
    public function documents()
    {
        return $this->hasMany(BeneficiaryEnclosure::class, 'application_id');
    }
    public function bank()
    {
        return $this->hasOne(BeneficiaryBankDetail::class, 'application_id', 'application_id');
    }
    public function aadhaar()
    {
        return $this->hasOne(BeneficiaryAadhaar::class, 'application_id', 'application_id');
    }
    // public function contact()
    // {
    //     return $this->hasOne(BeneficiaryContactDetail::class, 'application_id', 'application_id');
    // }

    // public function transformAudit(array $data): array
    // {
    //     $data['new_values']['updated_by_role'] = Auth::user()->role_id;
    //     $data['new_values']['session_id'] = session()->getId();
    //     $data['new_values']['user_agent'] = \Illuminate\Support\Facades\Request::userAgent();
    //     $data['new_values']['url'] = \Illuminate\Support\Facades\Request::fullUrl();
    //     $data['new_values']['method'] = \Illuminate\Support\Facades\Request::method();
    //     $data['new_values']['referrer'] = \Illuminate\Support\Facades\Request::header('referer');
    //     return $data;
    // }    

    public function banks()
    {
        return $this->hasOne(BeneficiaryBankDetail::class, 'application_id', 'application_id');
    }
    public function enclosers()
    {
        return $this->hasMany(BeneficiaryEnclosure::class, 'application_id', 'application_id');
    }
    public function failedPaymentDetails()
    {
        return $this->hasOne(FailedPaymentDetails::class, 'ben_id', 'beneficiary_id');
    }
    public function benPaymentDetails()
    {
        return $this->hasOne(BenPaymentDetails::class, 'ben_id', 'beneficiary_id');
    }
    public function scheme()
    {
        return $this->hasOne(Scheme::class, 'id', 'scheme_id');
    }
    public function creator()
    {
        $block = Block::where('lgd_code', $this->created_by_local_body_code)->first();
        if ($block) {
            return 1;
        }
        $subdivision = Subdivision::where('ref_code', $this->created_by_local_body_code)->first();
        if ($subdivision) {
            return 2;
        }
    }

}

