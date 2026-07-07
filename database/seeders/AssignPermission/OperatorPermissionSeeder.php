<?php

namespace Database\Seeders\AssignPermission;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleSchemeOfficeMapping;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class OperatorPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view draft list',
            'edit draft',
            'view beneficiaries',
            'view reports',
            'Duare Sarkar Entry',
            'Normal Entry',
            'submit-lb-form',
            'view caste modification list',
            'edit caste',
            'update caste',
            'sarasori-mukhyamantri',
            'cmo-grievance-mark',
            'view-mis-report',
        ];

        // 1) find role
        try {
            $role = Role::findByName('Operator');
        } catch (\Exception $e) {
            $this->command->error('Role "operator" not found. Seeder aborted.');

            return;
        }

        // Ensure permission records exist and collect Permission models
        $permissionModels = [];
        foreach ($permissions as $permName) {
            $permissionModels[] = Permission::firstOrCreate(
                ['name' => $permName],
                ['guard_name' => 'web']
            );
        }
        // Get user_ids from mapping table for that role
        $mappings = UserRoleSchemeOfficeMapping::where('role_id', $role->id)->get();

        if ($mappings->isEmpty()) {
            $this->command->info('No users found in UserRoleSchemeOfficeMapping for role "Operator".');

            return;
        }

        // 4) Loop mappings and assign permissions
        foreach ($mappings as $mapping) {
            $user = User::find($mapping->user_id);
            if (! $user) {
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

        $this->command->info('GivePermissionToOperatorSeeder finished.');
    }
}
