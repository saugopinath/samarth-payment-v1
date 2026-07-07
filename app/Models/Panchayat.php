<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Panchayat extends Model
{
    protected $fillable = [
            'name',
            'ref_code',
            'lgd_code',
            'block_id',
        ];

    

    public function Block(): BelongsTo
    {
         return $this->BelongsTo(Block::class);
        
    }
}
