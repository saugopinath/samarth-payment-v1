<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\Auth;

class Role extends SpatieRole implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'roles';
    public $timestamps = true;

    public $audit_old_permissions;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'guard_name'     => 'web',
        // 'can_manage_roles' => '[]',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        // 'parent_role_id',
        // 'can_manage_roles'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    public function parentRole()
    {
        return $this->belongsTo(self::class, 'parent_role_id');
    }

    public function childRoles()
    {
        return $this->hasMany(self::class, 'parent_role_id');
    }
    public function MapOfficeType(): HasMany
    {
        return $this->hasMany(RoleOfficeTypeMapping::class);
    }
    public function mappedPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,         // related model
            'role_has_permissions',    // pivot table
            'role_id',                 // foreign key on pivot for this model (Role)
            'permission_id'            // related key on pivot (Permission)
        );
    }

    public function transformAudit(array $data): array
    {
        $userId = Auth::id();
        $userRole = UserRoleSchemeOfficeMapping::where('user_id', $userId)
            ->value('role_id');
        $data['tags'] = class_basename($this) . '_' . $data['event'];
        $data['session_id'] = session()->getId();
        // $data['other_details'] = [
        //     'updated_by_role' => $userRole,
        //     'user_agent' => \Illuminate\Support\Facades\Request::userAgent(),
        //     'url' => \Illuminate\Support\Facades\Request::fullUrl(),
        //     'method' => \Illuminate\Support\Facades\Request::method(),
        //     'referrer' => \Illuminate\Support\Facades\Request::header('referer'),
        // ];
        $data['other_details'] = json_encode([
            'updated_by_role' => $userRole,
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'referrer' => request()->header('referer'),
        ]);
        if (app()->has('livewire_action_log_id')) {
            $data['livewire_action_log_id'] = (string) app('livewire_action_log_id');
        }
        if (app()->has('user_page_visit_log_id')) {
            $data['user_page_visit_log_id'] = (string) app('user_page_visit_log_id');
        }

        if ($data['event'] === 'updated' && isset($this->audit_old_permissions)) {
            $data['old_values']['permissions'] = $this->audit_old_permissions;
            $data['new_values']['permissions'] = $this->permissions->pluck('name')->toArray();
        }

        return $data;
    }
}
