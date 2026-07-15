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
 * Class Ward
 * 
 * @property int $id
 * @property string $lgd_code
 * @property string $ref_code
 * @property string $name
 * @property int $municipality_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * @property int|null $ward_number
 * 
 * @property Municipality $municipality
 * @property Collection|OfficeMaster[] $office_masters
 *
 * @package App\Models
 */
class Ward extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'wards';

	protected $casts = [
		'municipality_id' => 'int',
		'is_active' => 'int',
		'ward_number' => 'int'
	];

	protected $fillable = [
		'lgd_code',
		'ref_code',
		'name',
		'municipality_id',
		'is_active',
		'ward_number'
	];

	public function municipality()
	{
		return $this->belongsTo(Municipality::class);
	}

	public function office_masters()
	{
		return $this->hasMany(OfficeMaster::class);
	}
}
