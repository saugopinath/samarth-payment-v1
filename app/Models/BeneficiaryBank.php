<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BeneficiaryBank extends Model implements Auditable
{
    use \App\Traits\ZoneAwareModel;
    use \OwenIt\Auditing\Auditable;
    protected $guarded = [
        'id',
    ];
    // protected $primaryKey = 'beneficiary_id';
    protected $table = 'lb_scheme.beneficiary_banks';
    protected $fillable = [
        'application_id',
        'beneficiary_id',
        'bank_account_number',
        'ifsc',
        'bank_name',
        'branch_name',
        'created_by',
        'updated_by',
    ];

    public function ifscCodeMaster()
    {
        return $this->belongsTo(IfscCodeMaster::class, 'ifsc', 'code');
    }

    public function ifscbranch()
    {
        return $this->belongsTo(IfscCodeMaster::class, 'ifsc', 'code');
    }

    // public function beneficiaryPersonal()
    // {
    //     return $this->belongsTo(BeneficiaryPersonal::class, 'beneficiary_id', 'beneficiary_id');
    // }

    // public function beneficiaryFaultyPersonal()
    // {
    //     return $this->belongsTo(FaultyBeneficiaryPersonal::class, 'beneficiary_id', 'beneficiary_id');
    // }
    public function ifscMaster()
    {
        return $this->belongsTo(Ifsccodemaster::class, 'ifsc', 'code');
    }

    public function bankname()
    {
        $ifsc = $this->ifscbranch;
        $accno = $this->bank_account_number;
        if ($ifsc && $ifsc->bank) {
            return [
                'bank_name'   => $ifsc->bank->name,
                'branch_name' => $ifsc->branch,
                'ifsc_code'   => $ifsc->code,
                'accno' => $accno,
            ];
        }
    }
}

