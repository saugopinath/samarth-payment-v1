<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DsPhase;

class DsPhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DsPhase::insert([
            [
                'phase_code' => 1,
                'phase_desc' => 'Phase-I',
                'base_dob' => '2020-01-01',
                'is_current' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'phase_code' => 2,
                'phase_desc' => 'Phase-II',
                'base_dob' => '2021-01-01',
                'is_current' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'phase_code' => 3,
                'phase_desc' => 'Phase-III',
                'base_dob' => '2022-01-01',
                'is_current' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'phase_code' => 4,
                'phase_desc' => 'Phase-IV',
                'base_dob' => '2023-01-01',
                'is_current' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'phase_code' => 5,
                'phase_desc' => 'Phase-V',
                'base_dob' => '2024-01-01',
                'is_current' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'phase_code' => 6,
                'phase_desc' => 'Phase-VI',
                'base_dob' => '2025-01-01',
                'is_current' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
