<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IfmsLotMasterAdditionalInfo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ifms.lot_master_additional_info';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The primary key associated with the table.
     * Eloquent doesn't support composite primary keys without traits,
     * so we set this to null to prevent save/find issues.
     *
     * @var string|null
     */
    protected $primaryKey = null;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
