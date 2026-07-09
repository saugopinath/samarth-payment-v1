<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Scheme
 * 
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property string|null $description
 * @property int $department_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * @property int|null $min_age
 * @property int|null $max_age
 * @property float|null $base_amount
 * @property string|null $display_name
 * @property bool $allow_entry
 * @property bool $allow_verification
 * @property bool $allow_approval
 * @property bool $allow_regular_lot
 * @property bool $allow_arrear_lot
 * 
 * @property Department $department
 * @property Collection|SchemePaymentAmount[] $scheme_payment_amounts
 * @property Collection|User[] $users
 * @property Collection|Role[] $roles
 *
 * @package App\Models
 */
class Scheme extends Model
{
	protected $table = 'schemes';

	protected $casts = [
		'department_id' => 'int',
		'is_active' => 'int',
		'min_age' => 'int',
		'max_age' => 'int',
		'base_amount' => 'float',
		'allow_entry' => 'bool',
		'allow_verification' => 'bool',
		'allow_approval' => 'bool',
		'allow_regular_lot' => 'bool',
		'allow_arrear_lot' => 'bool'
	];

	protected $fillable = [
		'name',
		'short_name',
		'description',
		'department_id',
		'is_active',
		'min_age',
		'max_age',
		'base_amount',
		'display_name',
		'allow_entry',
		'allow_verification',
		'allow_approval',
		'allow_regular_lot',
		'allow_arrear_lot'
	];

	public function department()
	{
		return $this->belongsTo(Department::class);
	}

	public function scheme_payment_amounts()
	{
		return $this->hasMany(SchemePaymentAmount::class);
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'user_role_scheme_office_mappings')
					->withPivot('id', 'role_id', 'office_id', 'is_active')
					->withTimestamps();
	}

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'user_role_scheme_office_mappings')
					->withPivot('id', 'user_id', 'office_id', 'is_active')
					->withTimestamps();
	}
}
