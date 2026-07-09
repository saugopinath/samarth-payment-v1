<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CriticalChangeMaster
 * 
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int|null $code
 * @property int $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class CriticalChangeMaster extends Model
{
	protected $table = 'critical_change_masters';

	protected $casts = [
		'code' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'name',
		'short_name',
		'code',
		'is_active'
	];
}
