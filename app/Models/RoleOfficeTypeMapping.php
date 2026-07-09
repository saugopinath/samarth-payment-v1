<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RoleOfficeTypeMapping
 * 
 * @property int $id
 * @property int $office_type_id
 * @property int $role_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Role $role
 *
 * @package App\Models
 */
class RoleOfficeTypeMapping extends Model
{
	protected $table = 'role_office_type_mappings';

	protected $casts = [
		'office_type_id' => 'int',
		'role_id' => 'int'
	];

	protected $fillable = [
		'office_type_id',
		'role_id'
	];

	public function role()
	{
		return $this->belongsTo(Role::class);
	}
}
