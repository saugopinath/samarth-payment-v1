<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DuareSarkarPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $parent = Permission::firstOrCreate(
            ['name' => 'Duare Sarkar Entry Permission', 'guard_name' => 'web'],
            ['parent_id' => null]
        );

        $childPermissions = [
            'Duare Sarkar Entry Allow',
            'Duare Sarkar Entry Verification Allow',
            'Duare Sarkar Entry Approver Allow',
            'Duare Sarkar Entry Reject Allow',
            'Duare Sarkar Entry Revert Allow',
            'Bulk Actions Duare Sarkar Entry Verification Allow',
            'Bulk Actions Duare Sarkar Entry Approver Allow',
            'Bulk Actions Duare Sarkar Entry Reject Allow',
            'Bulk Actions Duare Sarkar Entry Revert Allow',
        ];

        foreach ($childPermissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['parent_id' => $parent->id]
            );
        }

        $this->command->info('✅ Duare Sarkar Permission and its child permissions seeded successfully!');
    }
}
