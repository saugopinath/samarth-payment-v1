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
 * Class Department
 * 
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property string|null $logo
 * @property int $state_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property State $state
 * @property Collection|UserPersonal[] $user_personals
 * @property Collection|Scheme[] $schemes
 *
 * @package App\Models
 */
class Department extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'departments';

	protected $casts = [
		'state_id' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'name',
		'short_name',
		'logo',
		'state_id',
		'is_active'
	];

	public function state()
	{
		return $this->belongsTo(State::class);
	}

	public function user_personals()
	{
		return $this->hasMany(UserPersonal::class);
	}

	public function schemes()
	{
		return $this->hasMany(Scheme::class);
	}
}
