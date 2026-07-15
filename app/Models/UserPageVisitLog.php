<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
/**
 * Class UserPageVisitLog
 * 
 * @property int $id
 * @property int|null $user_id
 * @property int|null $user_role_id
 * @property Carbon|null $visit_time
 * @property string|null $ip
 * @property string|null $user_agent
 * @property string|null $platform
 * @property string|null $browser
 * @property string|null $browser_version
 * @property string|null $url
 * @property string|null $method
 * @property string|null $referrer
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $session_id
 * @property string $log_level
 * @property string|null $log_nickname
 * @property string|null $request_payload
 * @property string|null $response_payload
 * @property int|null $status_code
 *
 * @package App\Models
 */
class UserPageVisitLog extends Model
{
	
	protected $table = 'user_page_visit_logs';

	protected $casts = [
		'user_id' => 'int',
		'user_role_id' => 'int',
		'visit_time' => 'datetime',
		'description' => 'binary',
		'request_payload' => 'binary',
		'response_payload' => 'binary',
		'status_code' => 'int'
	];

	protected $fillable = [
		'user_id',
		'user_role_id',
		'visit_time',
		'ip',
		'user_agent',
		'platform',
		'browser',
		'browser_version',
		'url',
		'method',
		'referrer',
		'description',
		'session_id',
		'log_level',
		'log_nickname',
		'request_payload',
		'response_payload',
		'status_code'
	];
}
