<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class NormalEntryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $parent = Permission::firstOrCreate(
            ['name' => 'Normal Entry Permission', 'guard_name' => 'web'],
            ['parent_id' => null]
        );

        $childPermissions = [
            'Normal Entry Allow',
            'Normal Entry Verification Allow',
            'Normal Entry Approver Allow',
            'Normal Entry Reject Allow',
            'Normal Entry Revert Allow',
            'Bulk Actions Normal Entry Verification Allow',
            'Bulk Actions Normal Entry Approver Allow',
            'Bulk Actions Normal Entry Reject Allow',
            'Bulk Actions Normal Entry Revert Allow',
        ];

        foreach ($childPermissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['parent_id' => $parent->id]
            );
        }

        $this->command->info('✅ Normal Entry Permission and its child permissions seeded successfully!');
    }
}
