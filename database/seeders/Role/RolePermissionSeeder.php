<?php

namespace Database\Seeders\Role;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tableNames = config('permission.table_names');
        DB::table($tableNames['roles'])->truncate();
        DB::table($tableNames['permissions'])->truncate();
        DB::table($tableNames['model_has_permissions'])->truncate();
        DB::table($tableNames['model_has_roles'])->truncate();
        DB::table($tableNames['role_has_permissions'])->truncate();
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'Super Admin',
            'HOD',
            'DDO',
            'Delegated DDO',
            'Mis User',
            'Delegated HOD',
            'Approver',
            'Delegated Approver',
            'Verifier',
            'Delegated Verifier',
            'Operator'
        ];
        $can_roles_manages = [
            'Super Admin' => ['HOD','DDO','Delegated DDO','Mis User'],
            'HOD' =>'',
            'DDO' => '',
            'Delegated DDO' => '',
            'Mis User' => ''
        ];

        $permissions = [
            // Validation Lot
            'create validation lot',
            'push validation lot',
            'ack validation lot',
            'get validation lot response',
            'import validation lot response',
            'defunc validation lot',
            // Payment  Lot 
            'create payment lot',
            'sign payment lot',
            'push payment lot',
            'ack payment lot',
            'get payment lot response',
            'import payment lot response',
            'defunc payment lot',
            'view-mis-report',
             // Configuration
            'configure-block-unblock-payment',
            'configure-financial-year',
            'configure-payment-lot-generation'

        ];
        $rolesArr = collect($roles)->map(function ($role) {
            return [
                'name' => $role,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        $permissionsArr = collect($permissions)->map(function ($permission) {
            return [
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        Role::insert($rolesArr);
        Permission::insert($permissionsArr);

        $role_permission_map = [
            'Super Admin' => [
            // Validation Lot
            'defunc validation lot',
           
            // Payment  Lot 
            'defunc payment lot',
           
             // Configuration
            'configure-block-unblock-payment',
            'configure-financial-year',
            'configure-payment-lot-generation'
            ],
             'DDO' => [
                // Validation Lot
            'create validation lot',
            'push validation lot',
            'ack validation lot',
            'get validation lot response',
            'import validation lot response',
            'defunc validation lot',
            // Payment  Lot 
            'create payment lot',
            'sign payment lot',
            'push payment lot',
            'ack payment lot',
            'get payment lot response',
            'import payment lot response',
            'defunc payment lot',
            'view-mis-report',
             // Configuration
            'configure-block-unblock-payment',
            'configure-financial-year',
            'configure-payment-lot-generation'
            ],
           
            'Delegated DDO' => [
                // Validation Lot
            'create validation lot',
            'push validation lot',
            'ack validation lot',
            'get validation lot response',
            'import validation lot response',
            'defunc validation lot',
            // Payment  Lot 
            'create payment lot',
            'sign payment lot',
            'push payment lot',
            'ack payment lot',
            'get payment lot response',
            'import payment lot response',
            'defunc payment lot',
            'view-mis-report',
             // Configuration
            'configure-block-unblock-payment',
            'configure-financial-year',
            'configure-payment-lot-generation'
            ],
            'Mis User' => [
                // Validation Lot
            'view-mis-report'
           
            ],
           
        ];

        foreach ($role_permission_map as $role_name => $permissions) {
            $role = Role::findByName($role_name);
            $role->givePermissionTo($permissions);
        }

    }
}
