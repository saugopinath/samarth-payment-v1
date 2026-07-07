<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Codemaster;

class PaymentLotMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masters = [
            'payment_type' => [
                6001 => 'Account Based',
                6002 => 'Aadhaar Based'
            ],
            'lot_month' => [
                6011 => 'January',
                6012 => 'February',
                6013 => 'March',
                6014 => 'April',
                6015 => 'May',
                6016 => 'June',
                6017 => 'July',
                6018 => 'August',
                6019 => 'September',
                6020 => 'October',
                6021 => 'November',
                6022 => 'December'
            ],
            'financial_year' => [
                6031 => '2022-2023',
                6032 => '2023-2024',
                6033 => '2024-2025',
            ],
            'target_payment_mode' => [
                6041 => 'IFMS',
                6042 => 'SBI',
                6043 => 'BANDHAN'
            ],
            'lot_type' => [
                6051 => 'Regular Lot',
                6052 => 'Arrer Lot'
            ]
        ];

        foreach ($masters as $parentShortCode => $items) {
            foreach ($items as $code => $name) {
                Codemaster::updateOrCreate(
                    [
                        'code' => $code,
                    ],
                    [
                        'parent_short_code' => $parentShortCode,
                        'name' => $name,
                        'short_name' => $name,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
