<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DsMapRecord
 * 
 * @property int $id
 * @property int $application_id
 * @property int|null $new_ds_phase
 * @property Carbon|null $new_ds_date
 * @property string|null $new_ds_registration_no
 * @property int|null $old_ds_phase
 * @property Carbon|null $old_ds_date
 * @property string|null $old_ds_registration_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class DsMapRecord extends Model
{
	protected $table = 'ds_map_records';

	protected $casts = [
		'application_id' => 'int',
		'new_ds_phase' => 'int',
		'new_ds_date' => 'datetime',
		'old_ds_phase' => 'int',
		'old_ds_date' => 'datetime'
	];

	protected $fillable = [
		'application_id',
		'new_ds_phase',
		'new_ds_date',
		'new_ds_registration_no',
		'old_ds_phase',
		'old_ds_date',
		'old_ds_registration_no'
	];
}
