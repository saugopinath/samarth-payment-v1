<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiaryContactDetail extends BaseAuditableModel
{
    use \App\Traits\ZoneAwareModel;
    protected $table = "pension.beneficiary_contacts";
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
    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'blockurban', 'id');
    }

    // public function block()
    // {
    //     return $this->belongsTo(Block::class, 'blockurban');
    // }
    // public function district()
    // {
    //     return $this->belongsTo(District::class, 'district_id', 'id');
    // }

    public function block()
    {
        return $this->belongsTo(Block::class, 'blockurban', 'id');
    }

    public function panchayat()
    {
        return $this->belongsTo(Panchayat::class, 'gpward');
        return $this->belongsTo(Panchayat::class, 'gpward', 'id');
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'gpward');
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function getFullAddress(): string
    {
        $district = optional($this->district)->name;
        $parts = [];

        if ($district) {
            $parts[] = "District - " . strtoupper($district);
        }
        // Rural
        if ($this->rural_urban == 2) {
            $block = optional($this->block)->name;
            $panchayat = optional($this->panchayat)->name;
            if ($block) {
                $parts[] = "Block - " . strtoupper($block);
            }
            if ($panchayat) {
                $parts[] = "GP - " . strtoupper($panchayat);
            }
        }
        // Urban
        else {
            $muni = $this->municipality;
            $municipality = optional($muni)->name;
            $subdivision = $muni ? optional($muni->Subdivision)->name : null;
            $ward = optional($this->ward)->name;

            if ($subdivision) {
                $parts[] = "Subdivision - " . strtoupper($subdivision);
            }
            if ($municipality) {
                $parts[] = "Municipality - " . strtoupper($municipality);
            }
            if ($ward) {
                $parts[] = "Ward - " . strtoupper($ward);
            }
        }

        // Use <br> for line breaks in HTML
        return !empty($parts) ? implode('<br>', $parts) : 'N/A';
    }

    public function blockmuni(): array
    {
        $blockname = '';
        $gpname = '';
        if ($this->rural_urban == 2) {
            $blockname = optional($this->block)->name;
            $gpname = optional($this->panchayat)->name;
        } else {
            $blockname = optional($this->municipality)->name;
            $gpname = optional($this->ward)->name;
        }
        return [
            'block' => $blockname ? strtoupper($blockname) : '',
            'gp' => $gpname ? strtoupper($gpname) : ''
        ];
        return $this->belongsTo(Ward::class, 'gpward', 'id');
    }

    // public function personal()
    // {
    //     return $this->belongsTo(
    //         BeneficiaryPersonalDetail::class,
    //         'application_id',
    //         'application_id'
    //     );
    // }

    // 🔥 Clean Address Accessor
    public function getFullAddressAttribute(): string
    {
        $parts = [];

        if ($this->district?->name) {
            $parts[] = strtoupper($this->district->name);
        }

        if ($this->rural_urban == 2) {
            if ($this->block?->name) {
                $parts[] = strtoupper($this->block->name);
            }

            if ($this->panchayat?->name) {
                $parts[] = strtoupper($this->panchayat->name);
            }
        } else {
            if ($this->ward?->name) {
                $parts[] = strtoupper($this->ward->name);
            }
        }

        return !empty($parts) ? implode(', ', $parts) : 'N/A';
    }
    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class, 'sub_division_id', 'id');
    }
}

