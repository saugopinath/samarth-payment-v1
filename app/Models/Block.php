<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Block
 * 
 * @property int $id
 * @property string|null $ref_code
 * @property string $lgd_code
 * @property string $name
 * @property string|null $local_name
 * @property int $district_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property District $district
 * @property Collection|Panchayat[] $panchayats
 * @property Collection|OfficeMaster[] $office_masters
 *
 * @package App\Models
 */
class Block extends Model
{
	protected $table = 'blocks';

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

	public function panchayats()
	{
		return $this->hasMany(Panchayat::class);
	}

	public function office_masters()
	{
		return $this->hasMany(OfficeMaster::class);
	}
}
