<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAuditTrail
 * 
 * @property int $id
 * @property string|null $old_password
 * @property string|null $new_password
 * @property int|null $operation_type
 * @property int|null $operate_by
 * @property int|null $operate_to_user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $operation_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class UserAuditTrail extends Model
{
	protected $table = 'user_audit_trails';

	protected $casts = [
		'operation_type' => 'int',
		'operate_by' => 'int',
		'operate_to_user_id' => 'int',
		'operation_time' => 'datetime'
	];

	protected $hidden = [
		'old_password',
		'new_password'
	];

	protected $fillable = [
		'old_password',
		'new_password',
		'operation_type',
		'operate_by',
		'operate_to_user_id',
		'ip_address',
		'user_agent',
		'operation_time'
	];
}
