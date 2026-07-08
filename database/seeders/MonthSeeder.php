<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Month;
use Carbon\Carbon;

class MonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $months = [
            ['name' => 'April', 'code' => 'APR', 'is_active' => true, 'display_order' => 1],
            ['name' => 'May', 'code' => 'MAY', 'is_active' => true, 'display_order' => 2],
            ['name' => 'June', 'code' => 'JUN', 'is_active' => true, 'display_order' => 3],
            ['name' => 'July', 'code' => 'JUL', 'is_active' => true, 'display_order' => 4],
            ['name' => 'August', 'code' => 'AUG', 'is_active' => true, 'display_order' => 5],
            ['name' => 'September', 'code' => 'SEP', 'is_active' => true, 'display_order' => 6],
            ['name' => 'October', 'code' => 'OCT', 'is_active' => true, 'display_order' => 7],
            ['name' => 'November', 'code' => 'NOV', 'is_active' => true, 'display_order' => 8],
            ['name' => 'December', 'code' => 'DEC', 'is_active' => true, 'display_order' => 9],
            ['name' => 'January', 'code' => 'JAN', 'is_active' => true, 'display_order' => 10],
            ['name' => 'February', 'code' => 'FEB', 'is_active' => true, 'display_order' => 11],
            ['name' => 'March', 'code' => 'MAR', 'is_active' => true, 'display_order' => 12],
        ];

        foreach ($months as &$month) {
            $month['created_at'] = $now;
            $month['updated_at'] = $now;
        }

        Month::insert($months);
    }
}
