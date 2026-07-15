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
 * Class UserRoleSchemeOfficeMapping
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property int $role_id
 * @property int $office_id
 * @property int $scheme_id
 * @property int $is_active
 * 
 * @property User $user
 * @property Role $role
 * @property OfficeMaster $office_master
 * @property Scheme $scheme
 *
 * @package App\Models
 */
class UserRoleSchemeOfficeMapping extends Model implements Auditable
{
	use AuditableTrait;
	protected $table = 'user_role_scheme_office_mappings';

	protected $casts = [
		'user_id' => 'int',
		'role_id' => 'int',
		'office_id' => 'int',
		'scheme_id' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'user_id',
		'role_id',
		'office_id',
		'scheme_id',
		'is_active'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function role()
	{
		return $this->belongsTo(Role::class);
	}

	public function office_master()
	{
		return $this->belongsTo(OfficeMaster::class, 'office_id');
	}

	public function scheme()
	{
		return $this->belongsTo(Scheme::class);
	}
}
