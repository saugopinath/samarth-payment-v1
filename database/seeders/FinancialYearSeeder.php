<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialYear;
use Carbon\Carbon;

class FinancialYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        FinancialYear::insert([
            ['name' => '2023-2024', 'code' => '2023-2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '2024-2025', 'code' => '2024-2025', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '2025-2026', 'code' => '2025-2026', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '2026-2027', 'code' => '2026-2027', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
