<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DsPhase
 * 
 * @property int $id
 * @property int $phase_code
 * @property string $phase_desc
 * @property bool $is_current
 * @property Carbon $base_dob
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class DsPhase extends Model
{
	protected $table = 'ds_phases';

	protected $casts = [
		'phase_code' => 'int',
		'is_current' => 'bool',
		'base_dob' => 'datetime'
	];

	protected $fillable = [
		'phase_code',
		'phase_desc',
		'is_current',
		'base_dob'
	];
}
