<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Activitylog\Models\Concerns\LogsActivity;


class User extends Authenticatable implements Auditable, JWTSubject
{
    use HasFactory, HasRoles, Notifiable;
    use LogsActivity;
    use \OwenIt\Auditing\Auditable;

    public $audit_old_permissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'scheme_id',
        'password',
        'two_factor_code',
        'two_factor_expires_at',
        'flag_sent_otp',
        'password_set_time',
        'password_expires_at',
        'updated_at',
        'mobile_no',
        'is_active',
        'designation',
        'bypass_otp',
        'current_session_id',
        'allow_multi_session',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'bypass_otp' => 'boolean',
        ];
    }

    public function RoleSchemeOfficeMappings(): HasMany
    {

        return $this->hasMany(UserRoleSchemeOfficeMapping::class);
    }

    public function mappedRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_role_scheme_office_mappings',
            'user_id',
            'role_id'
        )
            ->wherePivot('is_active', 1)
            ->where('roles.id', '!=', 10);
    }

    /**
     * Direct permissions assigned to the user (not via roles)
     */
    public function mappedPermissions(): BelongsToMany
    {
        return $this->morphToMany(
            Permission::class,
            'model',
            'model_has_permissions',
            'model_id',
            'permission_id'
        )->withPivot('scheme_id');
    }

    public function givePermissionWithScheme($permissionId, $schemeId)
    {
        // Check already exists
        $exists = $this->mappedPermissions()
            ->wherePivot('permission_id', $permissionId)
            ->wherePivot('scheme_id', $schemeId)
            ->exists();

        if (! $exists) {

            $this->mappedPermissions()->attach(
                $permissionId,
                [
                    'scheme_id' => $schemeId,
                ]
            );

        }
    }

    public function transformAudit(array $data): array
    {
        $userId = Auth::id();
        $userRole = UserRoleSchemeOfficeMapping::where('user_id', $userId)
            ->value('role_id');

        $data['tags'] = class_basename($this).'_'.($data['event'] ?? 'unknown');
        $data['session_id'] = session()->getId();

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

        // ⭐ Custom: Include Permissions in the Audit
        if ($data['event'] === 'updated' && isset($this->audit_old_permissions)) {
            $data['old_values']['permissions'] = $this->audit_old_permissions;
            $data['new_values']['permissions'] = $this->permissions->pluck('name')->toArray();
        }

        return $data;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class)->orderBy('created_at', 'desc');
    }

    public function recordPasswordHistory($hashedPassword, $limit = 5)
    {
        $this->passwordHistories()->create(['password' => $hashedPassword]);

        // Keep only the last $limit passwords
        $histories = $this->passwordHistories()->pluck('id');
        if ($histories->count() > $limit) {
            PasswordHistory::whereIn('id', $histories->slice($limit))->delete();
        }
    }
}
