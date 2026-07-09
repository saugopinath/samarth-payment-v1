<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelHasPermission
 * 
 * @property int $permission_id
 * @property string $model_type
 * @property int $model_id
 * @property int|null $scheme_id
 * 
 * @property Permission $permission
 *
 * @package App\Models
 */
class ModelHasPermission extends Model
{
	protected $table = 'model_has_permissions';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'permission_id' => 'int',
		'model_id' => 'int',
		'scheme_id' => 'int'
	];

	protected $fillable = [
		'permission_id',
		'model_type',
		'model_id',
		'scheme_id'
	];

	public function permission()
	{
		return $this->belongsTo(Permission::class);
	}
}
