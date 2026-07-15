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
 * Class Bankmaster
 * 
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property string $bank_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_active
 * 
 * @property Collection|Ifsccodemaster[] $ifsccodemasters
 *
 * @package App\Models
 */
class Bankmaster extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'bankmasters';

	protected $casts = [
		'is_active' => 'int'
	];

	protected $fillable = [
		'name',
		'short_name',
		'bank_code',
		'is_active'
	];

	public function ifsccodemasters()
	{
		return $this->hasMany(Ifsccodemaster::class);
	}
}
