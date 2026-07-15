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
 * Class Permission
 * 
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $parent_id
 * @property bool $is_active
 * 
 * @property ModelHasPermission|null $model_has_permission
 * @property Collection|Role[] $roles
 *
 * @package App\Models
 */
class Permission extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'permissions';

	protected $casts = [
		'parent_id' => 'int',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'name',
		'guard_name',
		'parent_id',
		'is_active'
	];

	public function model_has_permission()
	{
		return $this->hasOne(ModelHasPermission::class);
	}

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'role_has_permissions');
	}
}
