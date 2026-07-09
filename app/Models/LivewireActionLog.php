<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LivewireActionLog
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property string|null $url
 * @property string|null $ip
 * @property string|null $component_name
 * @property string|null $method_name
 * @property string|null $request_payload
 * @property string|null $response_payload
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $log_level
 * @property string|null $log_nickname
 * @property string|null $user_page_visit_log_id
 *
 * @package App\Models
 */
class LivewireActionLog extends Model
{
	protected $table = 'livewire_action_logs';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'session_id',
		'url',
		'ip',
		'component_name',
		'method_name',
		'request_payload',
		'response_payload',
		'log_level',
		'log_nickname',
		'user_page_visit_log_id'
	];
}
