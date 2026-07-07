<?php

namespace Database\Seeders\AssignPermission;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\UserRoleSchemeOfficeMapping;

use Spatie\Permission\PermissionRegistrar;

class GivePermissionToAdminSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'RolePermissionManagement',
            'UserManagement',
            'DutyAssignManagement',
            'OfficeManagement',
            'create role mappings',
            'manage role mappings',
            'view offices',
            'create offices',
            'view users',
            'create users',
            'user create',
            'view user permission',
            'view permission',
            'master-tab',
            'role-rank-management',
            'define-workflow',
            'role-permission-management',
            'scheme-capacity-setting',
            'import-janma-mrityu-data',
            'dynamic-workflow-management',
            'cmo-data-fetch',
            'view-mis-report'
        ];
        // 1) find role
        try {
            $role = Role::findByName('Super Admin');
        } catch (\Exception $e) {
            $this->command->error('Role "Super Admin" not found. Seeder aborted.');
            return;
        }

        // 2) Ensure permission records exist and collect Permission models
        $permissionModels = [];
        foreach ($permissions as $permName) {
            $permissionModels[] = Permission::firstOrCreate(
                ['name' => $permName],
                ['guard_name' => 'web']
            );
        }

        // 3) Get mappings for that role
        $mappings = UserRoleSchemeOfficeMapping::where('role_id', $role->id)->get();

        if ($mappings->isEmpty()) {
            $this->command->info('No users found in UserRoleSchemeOfficeMapping for role "Super Admin".');
            return;
        }

        // 4) Loop mappings and assign permissions
        foreach ($mappings as $mapping) {
            $user = User::find($mapping->user_id);
            if (!$user) {
                $this->command->warn("User id={$mapping->user_id} not found (skipping).");
                continue;
            }

            // Set the permissions team ID for this scheme
            app(PermissionRegistrar::class)->setPermissionsTeamId($mapping->scheme_id);

            foreach ($permissionModels as $permission) {
                // check if user already has this permission
                if ($user->hasPermissionTo($permission->name)) {
                    $this->command->info("User id={$user->id} already has permission '{$permission->name}' for scheme {$mapping->scheme_id} (id={$permission->id}).");
                    continue;
                }
                // assign and print message
                $user->givePermissionTo($permission->name);
                $this->command->info("Assigned permission '{$permission->name}' (id={$permission->id}) to user id={$user->id} for scheme {$mapping->scheme_id}.");
            }
        }
        // Reset the permissions team ID
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $this->command->info('GivePermissionToAdminSeeder finished.');
    }
}
