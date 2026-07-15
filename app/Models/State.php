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
 * Class State
 * 
 * @property int $id
 * @property string|null $ref_code
 * @property string $lgd_code
 * @property string $name
 * @property string|null $local_name
 * @property string $state_ut
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property Collection|Department[] $departments
 * @property Collection|District[] $districts
 * @property Collection|Ifsccodemaster[] $ifsccodemasters
 * @property Collection|OfficeMaster[] $office_masters
 *
 * @package App\Models
 */
class State extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'states';

	protected $casts = [
		'is_active' => 'int'
	];

	protected $fillable = [
		'ref_code',
		'lgd_code',
		'name',
		'local_name',
		'state_ut',
		'is_active'
	];

	public function departments()
	{
		return $this->hasMany(Department::class);
	}

	public function districts()
	{
		return $this->hasMany(District::class);
	}

	public function ifsccodemasters()
	{
		return $this->hasMany(Ifsccodemaster::class);
	}

	public function office_masters()
	{
		return $this->hasMany(OfficeMaster::class);
	}
}
