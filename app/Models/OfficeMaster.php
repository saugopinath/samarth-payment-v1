<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OfficeMaster
 * 
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $zip
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $office_type_id
 * @property int $state_id
 * @property int|null $district_id
 * @property int|null $block_id
 * @property int|null $subdivision_id
 * @property int|null $municipalitiy_id
 * @property int|null $ward_id
 * @property int|null $panchayat_id
 * @property int $is_active
 * @property int|null $max_operator
 * @property int|null $max_verifier
 * @property int|null $max_enquiry_officer
 * @property int|null $parent_id
 * 
 * @property State $state
 * @property District|null $district
 * @property Block|null $block
 * @property Subdivision|null $subdivision
 * @property Municipality|null $municipality
 * @property Ward|null $ward
 * @property Panchayat|null $panchayat
 * @property OfficeMaster|null $office_master
 * @property Collection|OfficeMaster[] $office_masters
 * @property Collection|UserRoleSchemeOfficeMapping[] $user_role_scheme_office_mappings
 *
 * @package App\Models
 */
class OfficeMaster extends Model
{
	protected $table = 'office_masters';

	protected $casts = [
		'office_type_id' => 'int',
		'state_id' => 'int',
		'district_id' => 'int',
		'block_id' => 'int',
		'subdivision_id' => 'int',
		'municipalitiy_id' => 'int',
		'ward_id' => 'int',
		'panchayat_id' => 'int',
		'is_active' => 'int',
		'max_operator' => 'int',
		'max_verifier' => 'int',
		'max_enquiry_officer' => 'int',
		'parent_id' => 'int'
	];

	protected $fillable = [
		'name',
		'address',
		'zip',
		'office_type_id',
		'state_id',
		'district_id',
		'block_id',
		'subdivision_id',
		'municipalitiy_id',
		'ward_id',
		'panchayat_id',
		'is_active',
		'max_operator',
		'max_verifier',
		'max_enquiry_officer',
		'parent_id'
	];

	public function state()
	{
		return $this->belongsTo(State::class);
	}

	public function district()
	{
		return $this->belongsTo(District::class);
	}

	public function block()
	{
		return $this->belongsTo(Block::class);
	}

	public function subdivision()
	{
		return $this->belongsTo(Subdivision::class);
	}

	public function municipality()
	{
		return $this->belongsTo(Municipality::class, 'municipalitiy_id');
	}

	public function ward()
	{
		return $this->belongsTo(Ward::class);
	}

	public function panchayat()
	{
		return $this->belongsTo(Panchayat::class);
	}

	public function office_master()
	{
		return $this->belongsTo(OfficeMaster::class, 'parent_id');
	}

	public function office_masters()
	{
		return $this->hasMany(OfficeMaster::class, 'parent_id');
	}

	public function user_role_scheme_office_mappings()
	{
		return $this->hasMany(UserRoleSchemeOfficeMapping::class, 'office_id');
	}
}
