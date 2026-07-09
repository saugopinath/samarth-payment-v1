<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
class FinancialYear extends Model
{
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
