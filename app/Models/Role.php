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
 * Class Role
 * 
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $rank
 * @property bool $is_active
 * 
 * @property Collection|RoleOfficeTypeMapping[] $role_office_type_mappings
 * @property Collection|User[] $users
 * @property Collection|Scheme[] $schemes
 * @property Collection|ModelHasRole[] $model_has_roles
 * @property Collection|Permission[] $permissions
 *
 * @package App\Models
 */
class Role extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'roles';

	protected $casts = [
		'rank' => 'int',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'name',
		'guard_name',
		'rank',
		'is_active'
	];

	public function role_office_type_mappings()
	{
		return $this->hasMany(RoleOfficeTypeMapping::class);
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'user_role_scheme_office_mappings')
					->withPivot('id', 'office_id', 'scheme_id', 'is_active')
					->withTimestamps();
	}

	public function schemes()
	{
		return $this->belongsToMany(Scheme::class, 'user_role_scheme_office_mappings')
					->withPivot('id', 'user_id', 'office_id', 'is_active')
					->withTimestamps();
	}

	public function model_has_roles()
	{
		return $this->hasMany(ModelHasRole::class);
	}

	public function permissions()
	{
		return $this->belongsToMany(Permission::class, 'role_has_permissions');
	}
}
