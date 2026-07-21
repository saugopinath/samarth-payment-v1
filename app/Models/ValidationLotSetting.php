<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * Class ValidationLotSetting
 * 
 * @property int $id
 * @property int $scheme_id
 * @property string $month
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ValidationLotSetting extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'validation_lot_settings';

    protected $casts = [
        'scheme_id' => 'int'
    ];

    protected $fillable = [
        'scheme_id',
        'type'
    ];
}
