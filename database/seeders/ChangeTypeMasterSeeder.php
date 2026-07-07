<?php

namespace Database\Seeders;

use App\Models\ChangeTypeMaster;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChangeTypeMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Name Change', 'short_name' => 'Name change allowed', 'code' => 0, 'is_active' => 1],
            ['name' => 'DOB Change', 'short_name' => 'dob change allowed', 'code' => 1, 'is_active' => 1],
            ['name' => 'DOB Change', 'short_name' => 'dob change allowed', 'code' => 1, 'is_active' => 1],
            ['name' => 'Address Change', 'short_name' => 'address change allowed', 'code' => 2, 'is_active' => 1],
            ['name' => 'Bank Details Change', 'short_name' => 'bank details change allowed', 'code' => 3, 'is_active' => 1],
            ['name' => 'Mobile Number Change', 'short_name' => 'mobile number change allowed', 'code' => 4, 'is_active' => 1],
        ];
        foreach ($data as $row) {
            ChangeTypeMaster::updateOrCreate(
                ['code' => $row['code']], 
                $row
            );
        }
    }
}
