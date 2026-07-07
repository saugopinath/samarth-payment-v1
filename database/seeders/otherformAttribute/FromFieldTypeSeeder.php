<?php

namespace Database\Seeders\OtherformAttribute;

use App\Models\FromFieldType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FromFieldTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'text',     'html_type' => 'text'],
            ['name' => 'number',   'html_type' => 'number'],
            ['name' => 'date',     'html_type' => 'date'],
            ['name' => 'select',   'html_type' => 'select'],
            ['name' => 'radio',    'html_type' => 'radio'],
            ['name' => 'checkbox', 'html_type' => 'checkbox'],
            ['name' => 'textarea', 'html_type' => 'textarea'],
            ['name' => 'file',     'html_type' => 'file'],
            ['name' => 'password', 'html_type' => 'password'],
        ];

        foreach ($types as $type) {
            FromFieldType::updateOrCreate(
                ['name' => $type['name']],     // UNIQUE KEY
                ['html_type' => $type['html_type']]
            );
        }
    }
}
