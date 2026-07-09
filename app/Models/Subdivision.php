<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Subdivision
 * 
 * @property int $id
 * @property string $ref_code
 * @property string|null $lgd_code
 * @property string $name
 * @property string|null $local_name
 * @property int $district_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property District $district
 * @property Collection|Municipality[] $municipalities
 * @property Collection|OfficeMaster[] $office_masters
 *
 * @package App\Models
 */
class Subdivision extends Model
{
	protected $table = 'subdivisions';

	protected $casts = [
		'district_id' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'ref_code',
		'lgd_code',
		'name',
		'local_name',
		'district_id',
		'is_active'
	];

	public function district()
	{
		return $this->belongsTo(District::class);
	}

	public function municipalities()
	{
		return $this->hasMany(Municipality::class);
	}

	public function office_masters()
	{
		return $this->hasMany(OfficeMaster::class);
	}
}
