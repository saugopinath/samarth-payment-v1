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
 * Class Ifsccodemaster
 * 
 * @property int $id
 * @property string $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $branch
 * @property int $state_id
 * @property int $bankmaster_id
 * @property int $is_active
 * 
 * @property State $state
 * @property Bankmaster $bankmaster
 *
 * @package App\Models
 */
class Ifsccodemaster extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'ifsccodemasters';

	protected $casts = [
		'state_id' => 'int',
		'bankmaster_id' => 'int',
		'is_active' => 'int'
	];

	protected $fillable = [
		'code',
		'branch',
		'state_id',
		'bankmaster_id',
		'is_active'
	];

	public function state()
	{
		return $this->belongsTo(State::class);
	}

	public function bankmaster()
	{
		return $this->belongsTo(Bankmaster::class);
	}
}
