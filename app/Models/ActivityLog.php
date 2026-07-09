<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityLog
 * 
 * @property int $id
 * @property string|null $log_name
 * @property string $description
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $causer_type
 * @property int|null $causer_id
 * @property string|null $properties
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $event
 * @property uuid|null $batch_uuid
 * @property string|null $session_id
 * @property string|null $attribute_changes
 *
 * @package App\Models
 */
class ActivityLog extends Model
{
	protected $table = 'activity_log';

	protected $casts = [
		'subject_id' => 'int',
		'causer_id' => 'int',
		'batch_uuid' => 'uuid'
	];

	protected $fillable = [
		'log_name',
		'description',
		'subject_type',
		'subject_id',
		'causer_type',
		'causer_id',
		'properties',
		'event',
		'batch_uuid',
		'session_id',
		'attribute_changes'
	];
}
