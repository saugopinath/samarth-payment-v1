<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $mobile_no
 * @property int $flag_sent_otp
 * @property bool|null $first_time_set_password
 * @property Carbon|null $password_set_time
 * @property Carbon|null $password_expires_at
 * @property string|null $last_otp
 * @property Carbon|null $last_otp_generation_time
 * @property Carbon|null $last_otp_expire_time
 * @property int $is_active
 * @property int $is_login
 * @property bool $bypass_otp
 * @property string|null $current_session_id
 * @property bool $allow_multi_session
 * @property string|null $designation
 * 
 * @property Collection|UserPersonal[] $user_personals
 * @property Collection|AcceptRejectInfo[] $accept_reject_infos
 * @property Collection|PasswordHistory[] $password_histories
 * @property Collection|Role[] $roles
 * @property Collection|Scheme[] $schemes
 *
 * @package App\Models
 */
class User extends Model
{
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime',
		'flag_sent_otp' => 'int',
		'first_time_set_password' => 'bool',
		'password_set_time' => 'datetime',
		'password_expires_at' => 'datetime',
		'last_otp_generation_time' => 'datetime',
		'last_otp_expire_time' => 'datetime',
		'is_active' => 'int',
		'is_login' => 'int',
		'bypass_otp' => 'bool',
		'allow_multi_session' => 'bool'
	];

	protected $hidden = [
		'password',
		'remember_token',
		'first_time_set_password'
	];

	protected $fillable = [
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token',
		'mobile_no',
		'flag_sent_otp',
		'first_time_set_password',
		'password_set_time',
		'password_expires_at',
		'last_otp',
		'last_otp_generation_time',
		'last_otp_expire_time',
		'is_active',
		'is_login',
		'bypass_otp',
		'current_session_id',
		'allow_multi_session',
		'designation'
	];

	public function user_personals()
	{
		return $this->hasMany(UserPersonal::class);
	}

	public function accept_reject_infos()
	{
		return $this->hasMany(AcceptRejectInfo::class);
	}

	public function password_histories()
	{
		return $this->hasMany(PasswordHistory::class);
	}

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'user_role_scheme_office_mappings')
					->withPivot('id', 'office_id', 'scheme_id', 'is_active')
					->withTimestamps();
	}

	public function schemes()
	{
		return $this->belongsToMany(Scheme::class, 'user_role_scheme_office_mappings')
					->withPivot('id', 'role_id', 'office_id', 'is_active')
					->withTimestamps();
	}
}
