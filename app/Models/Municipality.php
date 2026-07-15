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
 * Class Municipality
 * 
 * @property int $id
 * @property string $lgd_code
 * @property string|null $ref_code
 * @property string $name
 * @property string|null $local_name
 * @property int $subdivision_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property Subdivision $subdivision
 * @property Collection|Ward[] $wards
 * @property Collection|OfficeMaster[] $office_masters
 *
 * @package App\Models
 */
class Municipality extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'municipalities';

	protected $casts = [
		'subdivision_id' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'lgd_code',
		'ref_code',
		'name',
		'local_name',
		'subdivision_id',
		'is_active'
	];

	public function subdivision()
	{
		return $this->belongsTo(Subdivision::class);
	}

	public function wards()
	{
		return $this->hasMany(Ward::class);
	}

	public function office_masters()
	{
		return $this->hasMany(OfficeMaster::class, 'municipalitiy_id');
	}

    public function lotControl()
    {
        return $this->morphOne(\App\Models\LotControl::class, 'blockable');
    }
}
