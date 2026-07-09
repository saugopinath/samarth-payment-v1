<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VerificationCode
 * 
 * @property int $id
 * @property int $user_id
 * @property string $otp
 * @property string $mobile_no
 * @property Carbon|null $expire_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $status
 *
 * @package App\Models
 */
class VerificationCode extends Model
{
	protected $table = 'verification_codes';

	protected $casts = [
		'user_id' => 'int',
		'expire_at' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'otp',
		'mobile_no',
		'expire_at',
		'status'
	];
}
