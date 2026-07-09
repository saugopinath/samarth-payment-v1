<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Month
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $is_active
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Month extends Model
{
	protected $table = 'months';

	protected $casts = [
		'is_active' => 'bool',
		'display_order' => 'int'
	];

	protected $fillable = [
		'name',
		'code',
		'is_active',
		'display_order'
	];
}
