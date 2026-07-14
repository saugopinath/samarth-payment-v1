<?php

namespace Database\Seeders\AssignPermission;

use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\UserRoleSchemeOfficeMapping;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ApproverPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'viewlb applications',
            'Normal Entry Approver Allow',
            'Normal Entry Reject Allow',
            'Normal Entry Revert Allow',
            'view beneficiaries',
            'view reports',

            'view approver incomplete',
            'view users',
            'create users',
            'view caste modification list',
            'view beneficiary details',
            'TakeActionForCaste',
            'ApproveCasteApplication',
            'RevertCasteApplication',

            'lb-application-list',
            'Bulk Actions Normal Entry Approver Allow',
            'Bulk Actions Normal Entry Reject Allow',
            'Bulk Actions Normal Entry Revert Allow',
            'Bulk Actions Duare Sarkar Entry Approver Allow',
            'Bulk Actions Duare Sarkar Entry Reject Allow',
            'Bulk Actions Duare Sarkar Entry Revert Allow',
            'Duare Sarkar Entry Approver Allow',
            'Duare Sarkar Entry Reject Allow',
            'Duare Sarkar Entry Revert Allow',
            'Normal Entry Revert Allow',
            'Normal Entry Approver Allow',
            'Normal Entry Reject Allow',
            're-activate-death-incident',
            'manage-menus',
            'manage-permissions',
            'manage-roles',
            'manage-users',
            'manage-departments',
            'manage-schemes',
            'manage-menus',
            'manage-permissions',
            'manage-roles',
            'manage-users',
            'manage-departments',
            'manage-schemes',
            'modify caste',
            'back-from-jb',
            'back-from-jb-approver-button',
            'sarasori-mukhyamantri',
            'cmo-grievance-mark',
            'process-approve-application',
            'view-mis-report'
        ];
        try {
            $role = Role::findByName('Approver');
        } catch (\Exception $e) {
            $this->command->error('Role "Approver" not found. Seeder aborted.');

            return;
        }
        $permissionModels = [];
        foreach ($permissions as $permName) {
            $permissionModels[] = Permission::firstOrCreate(
                ['name' => $permName],
                ['guard_name' => 'web']
            );
        }
        // Get mappings for that role
        $mappings = UserRoleSchemeOfficeMapping::where('role_id', $role->id)->get();

        if ($mappings->isEmpty()) {
            $this->command->info('No users found in UserRoleSchemeOfficeMapping for role "Approver".');

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

        $this->command->info('Give Permission To Approver  finished.');
    }
}
