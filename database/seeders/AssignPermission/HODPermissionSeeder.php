<?php

namespace Database\Seeders\AssignPermission;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\UserRoleSchemeOfficeMapping;

use Spatie\Permission\Models\Permission;

class HODPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'sarasori-mukhyamantri',
            'cmo-grievance-mark',
            'view-mis-report'
        ];

        // 1️⃣ Find Role
        try {
            $role = Role::findByName('HOD');
        } catch (\Exception $e) {
            $this->command->error('Role "HOD" not found. Seeder aborted.');
            return;
        }

        // 2️⃣ Ensure permission exists
        $permissionModels = [];

        foreach ($permissions as $permName) {

            $permissionModels[] = Permission::firstOrCreate(
                [
                    'name' => $permName,
                    'guard_name' => 'web'
                ]
            );
        }

        // 3️⃣ Get mappings (WITH scheme_id)
        $mappings = UserRoleSchemeOfficeMapping::where('role_id', $role->id)
            ->get();

        if ($mappings->isEmpty()) {

            $this->command->info(
                'No users found in UserRoleSchemeOfficeMapping for role "HOD".'
            );

            return;
        }

        // 4️⃣ Assign permissions WITH scheme_id
        foreach ($mappings as $mapping) {

            $user = User::find($mapping->user_id);

            if (!$user) {

                $this->command->warn(
                    "User id={$mapping->user_id} not found (skipping)."
                );

                continue;
            }

            foreach ($permissionModels as $permission) {

                // Check if permission already exists
                $exists = DB::table('model_has_permissions')
                    ->where('permission_id', $permission->id)
                    ->where('model_id', $user->id)
                    ->where('model_type', User::class)
                    ->where('scheme_id', $mapping->scheme_id)
                    ->exists();

                if ($exists) {

                    $this->command->info(
                        "User id={$user->id} already has permission '{$permission->name}' with scheme_id={$mapping->scheme_id}."
                    );

                    continue;
                }

                // Insert manually with scheme_id
                DB::table('model_has_permissions')->insert([

                    'permission_id' => $permission->id,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                    'scheme_id' => $mapping->scheme_id,

                ]);

                $this->command->info(
                    "Assigned permission '{$permission->name}' to user id={$user->id} with scheme_id={$mapping->scheme_id}."
                );
            }
        }

        $this->command->info('HODPermissionSeeder finished successfully.');
    }
}
