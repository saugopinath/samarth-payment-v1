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
 * Class AcceptRejectInfo
 * 
 * @property int $id
 * @property int $scheme_id
 * @property int|null $application_id
 * @property int|null $beneficiary_id
 * @property string|null $ip_address
 * @property int|null $user_id
 * @property string|null $browser
 * @property string|null $model_name
 * @property int|null $op_type
 * @property int|null $revert_reason_cause_id
 * @property string|null $revert_reason_remarks
 * @property int|null $parent_id
 * @property string|null $old_op_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $critical_changes
 * @property string|null $old_value
 * @property string|null $new_value
 * 
 * @property User|null $user
 * @property Codemaster|null $codemaster
 *
 * @package App\Models
 */
class AcceptRejectInfo extends Model implements Auditable
{
	use AuditableTrait;
	protected $table = 'accept_reject_infos';

	protected $casts = [
		'scheme_id' => 'int',
		'application_id' => 'int',
		'beneficiary_id' => 'int',
		'user_id' => 'int',
		'op_type' => 'int',
		'revert_reason_cause_id' => 'int',
		'parent_id' => 'int',
		'critical_changes' => 'int',
		'old_value' => 'binary',
		'new_value' => 'binary'
	];

	protected $fillable = [
		'scheme_id',
		'application_id',
		'beneficiary_id',
		'ip_address',
		'user_id',
		'browser',
		'model_name',
		'op_type',
		'revert_reason_cause_id',
		'revert_reason_remarks',
		'parent_id',
		'old_op_type',
		'critical_changes',
		'old_value',
		'new_value'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function codemaster()
	{
		return $this->belongsTo(Codemaster::class, 'revert_reason_cause_id');
	}
}
