<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SchemePaymentAmount
 * 
 * @property int $id
 * @property int $scheme_id
 * @property string $financial_year
 * @property float $january_amount
 * @property float $february_amount
 * @property float $march_amount
 * @property float $april_amount
 * @property float $may_amount
 * @property float $june_amount
 * @property float $july_amount
 * @property float $august_amount
 * @property float $september_amount
 * @property float $october_amount
 * @property float $november_amount
 * @property float $december_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $january_payment_mode
 * @property string|null $february_payment_mode
 * @property string|null $march_payment_mode
 * @property string|null $april_payment_mode
 * @property string|null $may_payment_mode
 * @property string|null $june_payment_mode
 * @property string|null $july_payment_mode
 * @property string|null $august_payment_mode
 * @property string|null $september_payment_mode
 * @property string|null $october_payment_mode
 * @property string|null $november_payment_mode
 * @property string|null $december_payment_mode
 * 
 * @property Scheme $scheme
 *
 * @package App\Models
 */
class SchemePaymentAmount extends Model
{
	protected $table = 'scheme_payment_amounts';

	protected $casts = [
		'scheme_id' => 'int',
		'january_amount' => 'float',
		'february_amount' => 'float',
		'march_amount' => 'float',
		'april_amount' => 'float',
		'may_amount' => 'float',
		'june_amount' => 'float',
		'july_amount' => 'float',
		'august_amount' => 'float',
		'september_amount' => 'float',
		'october_amount' => 'float',
		'november_amount' => 'float',
		'december_amount' => 'float'
	];

	protected $fillable = [
		'scheme_id',
		'financial_year',
		'january_amount',
		'february_amount',
		'march_amount',
		'april_amount',
		'may_amount',
		'june_amount',
		'july_amount',
		'august_amount',
		'september_amount',
		'october_amount',
		'november_amount',
		'december_amount',
		'january_payment_mode',
		'february_payment_mode',
		'march_payment_mode',
		'april_payment_mode',
		'may_payment_mode',
		'june_payment_mode',
		'july_payment_mode',
		'august_payment_mode',
		'september_payment_mode',
		'october_payment_mode',
		'november_payment_mode',
		'december_payment_mode'
	];

	public function scheme()
	{
		return $this->belongsTo(Scheme::class);
	}
}
