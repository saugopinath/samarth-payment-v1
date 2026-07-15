<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class LotControl extends Model implements Auditable
{
     use AuditableTrait;
    use HasFactory;

    protected $fillable = [
        'allow_regular_lot',
        'allow_arrear_lot',
        'supporting_document',
        'last_block_by',
        'last_unblock_by',
        'last_block_at',
        'last_unblock_at',
        'last_block_ip',
        'last_unblock_ip',
    ];

    protected $casts = [
        'allow_regular_lot' => 'boolean',
        'allow_arrear_lot' => 'boolean',
        'last_block_at' => 'datetime',
        'last_unblock_at' => 'datetime',
    ];

    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }
}
