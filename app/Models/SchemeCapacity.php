<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SchemeCapacity
 * 
 * @property int $id
 * @property int $scheme_id
 * @property int $capacity_type
 * @property string $model_type
 * @property int $model_id
 * @property int $entry_type
 * @property string $total_capacity
 * @property string|null $extra_condition
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $action_type
 *
 * @package App\Models
 */
class SchemeCapacity extends Model
{
	protected $table = 'scheme_capacities';

	protected $casts = [
		'scheme_id' => 'int',
		'capacity_type' => 'int',
		'model_id' => 'int',
		'entry_type' => 'int',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'scheme_id',
		'capacity_type',
		'model_type',
		'model_id',
		'entry_type',
		'total_capacity',
		'extra_condition',
		'is_active',
		'action_type'
	];
}
