<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRoleSchemeOfficeMapping extends BaseAuditableModel
{
    protected $fillable = [
            'user_id',
            'office_id',
            'scheme_id',
            'role_id'
        ];
    protected static function booted(): void
    {

        /**
         * Call After Post Create
         */
        static::created(function (UserRoleSchemeOfficeMapping $mapdata) {
            $scheme_id = (int) $mapdata->scheme_id;
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($scheme_id);

            $user = User::find($mapdata->user_id);
            if ($user) {
                $role = Role::find($mapdata->role_id);
                if ($role) {
                    $user->assignRole($role);
                }
            }
        });

        /**
         * After Update
         */
        static::updated(function (UserRoleSchemeOfficeMapping $mapdata) {
            if ($mapdata->wasChanged('role_id')) {
                $oldRoleId = $mapdata->getOriginal('role_id');
                $newRoleId = $mapdata->role_id;
                $scheme_id = (int) $mapdata->scheme_id;

                app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($scheme_id);
                $user = User::find($mapdata->user_id);

                if ($user) {
                    $oldRole = Role::find($oldRoleId);
                    $newRole = Role::find($newRoleId);

                    if ($oldRole) {
                        $user->removeRole($oldRole);
                    }
                    if ($newRole) {
                        $user->assignRole($newRole);
                    }
                }
            }
        });

        /**
         * After Delete
         */
        static::deleted(function (UserRoleSchemeOfficeMapping $mapdata) {
            $scheme_id = (int) $mapdata->scheme_id;
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($scheme_id);

            $user = User::find($mapdata->user_id);
            if ($user) {
                $role = Role::find($mapdata->role_id);
                if ($role) {
                    $user->removeRole($role);
                }
            }
        });

    }
   public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function Office(): BelongsTo
    {
        return $this->belongsTo(OfficeMaster::class);
    }
    public function Scheme(): BelongsTo
    {
        return $this->belongsTo(Scheme::class);
    }
    public function Role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }



}
