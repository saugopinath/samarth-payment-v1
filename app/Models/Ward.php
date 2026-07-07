<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Ward extends Model
{
    protected $fillable = [
            'name',
            'ref_code',
            'lgd_code',
            'ward_number',
            'municipality_id',
        ];

    public function Municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
    public function Subdivision(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
    
    
    
}
