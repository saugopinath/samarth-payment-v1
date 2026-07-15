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
 * Class FinancialYearMonthLot
 * 
 * @property int $id
 * @property int $financial_year
 * @property string $month
 * @property bool $is_regular_lot
 * @property bool $is_arrear_lot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $scheme_id
 *
 * @package App\Models
 */
class FinancialYearMonthLot extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'financial_year_month_lots';

	protected $casts = [
		'financial_year' => 'int',
		'is_regular_lot' => 'bool',
		'is_arrear_lot' => 'bool',
		'scheme_id' => 'int'
	];

	protected $fillable = [
		'financial_year',
		'month',
		'is_regular_lot',
		'is_arrear_lot',
		'scheme_id'
	];
}
