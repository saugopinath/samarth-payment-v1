<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notification
 * 
 * @property int $id
 * @property string $title
 * @property string $message
 * @property string|null $scheme_name
 * @property string $type
 * @property string $status
 * @property string|null $meta
 * @property Carbon $notified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Notification extends Model
{
	protected $table = 'notifications';

	protected $casts = [
		'notified_at' => 'datetime'
	];

	protected $fillable = [
		'title',
		'message',
		'scheme_name',
		'type',
		'status',
		'meta',
		'notified_at'
	];
}
