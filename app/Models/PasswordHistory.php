<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PasswordHistory
 * 
 * @property int $id
 * @property int $user_id
 * @property string $password
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class PasswordHistory extends Model
{
	protected $table = 'password_histories';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'user_id',
		'password'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
