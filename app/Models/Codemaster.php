<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
/**
 * Class Codemaster
 * 
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int|null $parent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * @property string|null $code
 * @property int|null $rank
 * @property string|null $parent_short_code
 * 
 * @property Collection|AcceptRejectInfo[] $accept_reject_infos
 *
 * @package App\Models
 */
class Codemaster extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'codemasters';

	protected $casts = [
		'parent_id' => 'int',
		'is_active' => 'int',
		'rank' => 'int'
	];

	protected $fillable = [
		'name',
		'short_name',
		'parent_id',
		'is_active',
		'code',
		'rank',
		'parent_short_code'
	];

	public function accept_reject_infos()
	{
		return $this->hasMany(AcceptRejectInfo::class, 'revert_reason_cause_id');
	}
}
