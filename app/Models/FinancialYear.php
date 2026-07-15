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
 * Class FinancialYear
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class FinancialYear extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'financial_years';

	protected $casts = [
		'is_active' => 'bool'
	];

	protected $fillable = [
		'name',
		'code',
		'is_active'
	];
}
