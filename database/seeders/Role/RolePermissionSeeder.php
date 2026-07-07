<?php

namespace Database\Seeders\Role;

use App\Models\Permission;
use App\Models\Role;
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
            'Delegated HOD',
            'Approver',
            'Delegated Approver',
            'Verifier',
            'Delegated Verifier',
            'Operator',
            'Delegated Operator',
            'DDO',
            'Delegated DDO',
            'Mis User State',
            'Mis User District',
            'Mis User Block',
            'Mis User GP',
            'Mis User Sub Division',
            'Mis User Municipality',
            'Mis User Ward',
        ];
        $can_roles_manages = [
            'Super Admin' => ['HOD', 'Delegated HOD', 'Approver', 'Delegated Approver', 'Checker', 'Delegated Checker', 'Maker', 'Delegated Maker',
                'DDO', 'Delegated DDO', 'Mis User State', 'Mis User District', 'Mis User Block', 'Mis User GP',
                'Mis User Sub Division', 'Mis User Municipality', 'Mis User Ward'],

            'HOD' => ['Delegated HOD', 'Approver', 'Delegated Approver', 'Checker', 'Delegated Checker', 'Maker', 'Delegated Maker',
                'DDO', 'Delegated DDO', 'Mis User State', 'Mis User District', 'Mis User Block', 'Mis User GP',
                'Mis User Sub Division', 'Mis User Municipality', 'Mis User Ward'],
            'Delegated HOD' => '',
            'Approver' => ['Delegated Approver', 'Checker', 'Delegated Checker', 'Maker', 'Delegated Maker',
                'DDO', 'Delegated DDO', 'Mis User District', 'Mis User Block', 'Mis User GP',
                'Mis User Sub Division', 'Mis User Municipality', 'Mis User Ward'],
            'Delegated Approver' => '',
            'Checker' => ['Delegated Checker', 'Maker', 'Delegated Maker',
                'DDO', 'Delegated DDO', 'Mis User Block', 'Mis User GP',
                'Mis User Sub Division', 'Mis User Municipality', 'Mis User Ward'],
            'Delegated Checker' => '',
            'Maker' => '',
            'Delegated Maker' => '',
            'DDO' => '',
            'Delegated DDO' => '',
            'Mis User State' => '',
            'Mis User District' => '',
            'Mis User Block' => '',
            'Mis User GP' => '',
            'Mis User Sub Division' => '',
            'Mis User Municipality' => '',
            'Mis User Ward' => '',
        ];

        $permissions = [
            // Validation Lot
            'create validation lot',
            'push validation lot',
            'ack validation lot',
            'get validation lot response',
            'import validation lot response',
            // Payment  Lot SBI
            'create payment lot SBI',
            'sign payment lot SBI',
            'push payment lot SBI',
            'ack payment lot SBI',
            'get payment lot response SBI',
            'import payment lot response SBI',

            // Payment  Lot IFMS
            'create payment lot IFMS',
            'push payment lot IFMS',
            'ack payment lot IFMS',
            'submit to tresury IFMS',
            'sent to rbi IFMS',
            'get payment lot response IFMS',

            // User Model
            'create user',
            'read user',
            'update user',
            'delete user',

            // Role
            'create role',
            'read role',
            'update role',
            'delete role',
            // Permission
            'create permission',
            'read permission',
            'update permission',
            'delete permission',
            // Role Permission
            'create role permission',
            'read role permission',
            'update role permission',
            'delete role permission',
            // User Role
            'create user role',
            'read user role',
            'update user role',
            'delete user role',
            // User Permission
            'create user permission',
            'read user permission',
            'update user permission',
            'delete user permission',
            // State
            'create state',
            'read state',
            'update state',
            'delete state',
            // District
            'create district',
            'read district',
            'update district',
            'delete district',
            // Block
            'create block',
            'read block',
            'update block',
            'delete block',
            // Gp
            'create gp',
            'read gp',
            'update gp',
            'delete gp',
            // Municipality
            'create municipality',
            'read municipality',
            'update municipality',
            'delete municipality',
            // Ward
            'create ward',
            'read ward',
            'update ward',
            'delete ward',
            // Workflow
            'application list',
            'application view',
            'application entry',
            'application verify',
            'application approve',
            'application revert',
            'application reject',
            'application bulk approval',
            'application recommanded',
            'application recommanded bulk',
            'application entryverifyapproval',
            // Add your new permissions here
            'update bank',
            'search bank update',
            'update mobile',
            'view beneficiary details',
            'update bank details',
            'submit lb form',
            'view draft list',
            'edit draft',
            'view lb applications',
            'view application',
            'view beneficiaries',
            'revert incomplete',
            'manage role mappings',
            'create users',
            'create role mappings',
            'view offices',
            'view reports',
            'update caste',
            'edit caste',
            'view approved ba wise',
            'view approved list',
            'modify caste',
            'view caste modification list',
            'view incomplete applications',
            'view users',
            'update incomplete',
            'view verifier incomplete',
            'view approver incomplete',
            'view permission',
            'view user permission',
            'create offices',
            'update-mark-beneficiary-details',
            'update-beneficiary-list',
            'request-update-beneficiary',
            'process application',
            'verify application',
            'view-mis-report',

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
                // User Model
                'create user',
                'read user',
                // Role
                'create role',
                'read role',
                'update role',
                'delete role',
                // Permission
                'create permission',
                'read permission',
                'update permission',
                'delete permission',
                // Role Permission
                'create role permission',
                'read role permission',
                'update role permission',
                'delete role permission',
            ],
            'HOD' => [
                // Workflow
                'application list',
                'application view',
                'application recommanded',
                'application recommanded bulk',
            ],
            'Delegated HOD' => [
                // Workflow
                'application list',
                'application view',
                'application recommanded',
                'application recommanded bulk',
            ],
            'Approver' => [
                // District
                'read district',
                // Block
                'read block',
                // Municipality
                'read municipality',
                // Workflow
                'application list',
                'application view',
                'application approve',
                'application revert',
                'application reject',
                'application bulk approval',
            ],
            'Delegated Approver' => [
                // District
                'read district',
                // Block
                'read block',
                // Municipality
                'read municipality',
                // Workflow
                'application list',
                'application view',
                'application approve',
                'application revert',
                'application reject',
                'application bulk approval',
            ],
            'Verifier' => [
                // District
                'read district',
                // Block
                'read block',
                // Municipality
                'read municipality',
                // Workflow
                'application list',
                'application view',
                'application verify',
                'application revert',
                'application reject',
            ],
            'Delegated Verifier' => [
                // District
                'read district',
                // Block
                'read block',
                // Municipality
                'read municipality',
                // Workflow
                'application list',
                'application view',
                'application verify',
                'application revert',
                'application reject',
            ],
            'Operator' => [
                // District
                'read district',
                // Block
                'read block',
                // Municipality
                'read municipality',
                // Workflow
                'application view',
                'application list',
                'application entry',
            ],
            'Delegated Operator' => [
                // District
                'read district',
                // Block
                'read block',
                // Municipality
                'read municipality',
                // Workflow
                'application view',
                'application list',
                'application entry',

            ],
            'DDO' => [
                // Validation Lot
                'create validation lot',
                'push validation lot',
                'ack validation lot',
                'get validation lot response',
                'import validation lot response',
                // Payment  Lot SBI
                'create payment lot SBI',
                'sign payment lot SBI',
                'push payment lot SBI',
                'ack payment lot SBI',
                'get payment lot response SBI',
                'import payment lot response SBI',

                // Payment  Lot IFMS
                'create payment lot IFMS',
                'push payment lot IFMS',
                'ack payment lot IFMS',
                'submit to tresury IFMS',
                'sent to rbi IFMS',
                'get payment lot response IFMS',
            ],
            'Delegated DDO' => [
                // Validation Lot
                'create validation lot',
                'push validation lot',
                'ack validation lot',
                'get validation lot response',
                'import validation lot response',
                // Payment  Lot SBI
                'create payment lot SBI',
                'sign payment lot SBI',
                'push payment lot SBI',
                'ack payment lot SBI',
                'get payment lot response SBI',
                'import payment lot response SBI',

                // Payment  Lot IFMS
                'create payment lot IFMS',
                'push payment lot IFMS',
                'ack payment lot IFMS',
                'submit to tresury IFMS',
                'sent to rbi IFMS',
                'get payment lot response IFMS',
            ],
        ];

        foreach ($role_permission_map as $role_name => $permissions) {
            $role = Role::findByName($role_name);
            $role->givePermissionTo($permissions);
        }

    }
}
