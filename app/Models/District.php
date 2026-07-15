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
 * Class District
 * 
 * @property int $id
 * @property string|null $ref_code
 * @property string $lgd_code
 * @property string $name
 * @property string $short_name
 * @property string|null $local_name
 * @property int $state_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property State $state
 * @property Collection|Block[] $blocks
 * @property Collection|Subdivision[] $subdivisions
 * @property Collection|OfficeMaster[] $office_masters
 *
 * @package App\Models
 */
class District extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'districts';

	protected $casts = [
		'state_id' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'ref_code',
		'lgd_code',
		'name',
		'short_name',
		'local_name',
		'state_id',
		'is_active'
	];

	public function state()
	{
		return $this->belongsTo(State::class);
	}

	public function blocks()
	{
		return $this->hasMany(Block::class);
	}

	public function subdivisions()
	{
		return $this->hasMany(Subdivision::class);
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
