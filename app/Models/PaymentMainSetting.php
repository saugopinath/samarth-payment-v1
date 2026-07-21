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
class PaymentMainSetting extends Model implements Auditable
{
	 use AuditableTrait;
	protected $table = 'payment_main_settings';

	protected $casts = [
		'jan' => 'array',
		'feb' => 'array',
		'mar' => 'array',
		'apr' => 'array',
		'may' => 'array',
		'jun' => 'array',
		'jul' => 'array',
		'aug' => 'array',
		'sep' => 'array',
		'oct' => 'array',
		'nov' => 'array',
		'dec' => 'array',
	];

	protected $fillable = [
			'scheme_id',
			'financial_year',
			'jan',
            'feb',
            'mar',
            'apr',
            'may',
            'jun',
            'jul',
            'aug',
            'sep',
            'oct',
            'nov',
            'dec',
	];

	public function scheme()
	{
		return $this->belongsTo(Scheme::class);
	}
	public function FinancialYear()
	{
		return $this->belongsTo(FinancialYear::class,'financial_year','code');
	}
}
