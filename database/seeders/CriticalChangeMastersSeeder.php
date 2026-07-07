<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CriticalChangeMaster;

class CriticalChangeMastersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Caste Change', 'short_name' => 'caste_change', 'code' => 1, 'is_active' => 1],
            
        ];

        foreach ($data as $row) {
            CriticalChangeMaster::updateOrCreate(
                ['code' => $row['code']], // unique key
                $row
            );
        }
    }
}
