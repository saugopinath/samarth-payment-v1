<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
/**
 * Class Panchayat
 * 
 * @property int $id
 * @property string $lgd_code
 * @property string $ref_code
 * @property string $name
 * @property int $block_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property Block $block
 * @property Collection|OfficeMaster[] $office_masters
 *
 * @package App\Models
 */
class Panchayat extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'panchayats';

	protected $casts = [
		'block_id' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'lgd_code',
		'ref_code',
		'name',
		'block_id',
		'is_active'
	];

	public function block()
	{
		return $this->belongsTo(Block::class);
	}

	public function office_masters()
	{
		return $this->hasMany(OfficeMaster::class);
	}

    public function lotControl()
    {
        return $this->morphOne(\App\Models\LotControl::class, 'blockable');
    }
}
