<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelfDeclerationBasefield extends Model
{
    protected $table = 'self_decleration_basefields';
    protected $guarded = [];
    protected $casts = [
        'options' => 'array',
    ];
    public function sectionLevel()
    {
        return $this->belongsTo(
            SectionLevelMaster::class,
            'section_id'
        );
    }
}
