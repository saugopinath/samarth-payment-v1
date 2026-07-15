<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
/**
 * Class UserPersonal
 * 
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $full_name_as_in_aadhaar
 * @property string|null $picture
 * @property Carbon|null $date_hired
 * @property int|null $department_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property User $user
 * @property Department|null $department
 *
 * @package App\Models
 */
class UserPersonal extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'user_personals';

	protected $casts = [
		'user_id' => 'int',
		'date_hired' => 'datetime',
		'department_id' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'user_id',
		'name',
		'full_name_as_in_aadhaar',
		'picture',
		'date_hired',
		'department_id',
		'is_active'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function department()
	{
		return $this->belongsTo(Department::class);
	}
}
