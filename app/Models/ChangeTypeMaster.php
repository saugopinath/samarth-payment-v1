<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ChangeTypeMaster
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
class ChangeTypeMaster extends Model
{
	protected $table = 'change_type_masters';

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
