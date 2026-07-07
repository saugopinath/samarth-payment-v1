<?php

namespace Database\Seeders;

use App\Models\MasterTab;
use App\Models\SelfDeclerationBasefield;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SelfDeclarationBasefieldSeeder extends Seeder
{
    public function run(): void
    {
        $tabCode = MasterTab::where('tab_short_name', 'self_declaration')
            ->value('tab_code');
        if (!$tabCode) {
            $this->command->error('Self Declaration tab not found');
            return;
        }

        $fields = [
            [
                'field_name'        => 'resident',
                'level_name'      => 'I am a resident of West Bengal',
                'field_type'      => 'checkbox',
                'db_colunm'       => 'resident',
                'validation_rule' => 'required',
                'field_position'  => 1,
            ],
            [
                'field_name'        => 'no_govt_salary',
                'level_name'      => 'I do not earn any monthly remuneration from any regular Government job',
                'field_type'      => 'checkbox',
                'db_colunm'       => 'no_govt_salary',
                'validation_rule' => 'required',
                'field_position'  => 2,
            ],
            [
                'field_name'        => 'info_true',
                'level_name'      => 'All information and documents submitted are correct',
                'field_type'      => 'checkbox',
                'db_colunm'       => 'info_true',
                'validation_rule' => 'required',
                'field_position'  => 3,
            ],
            [
                'field_name'        => 'aadhaar_consent',
                'level_name'      => 'I give consent to Aadhaar authentication',
                'field_type'      => 'checkbox',
                'db_colunm'       => 'aadhaar_consent',
                'validation_rule' => 'nullable',
                'field_position'  => 4,
            ],
        ];

        foreach ($fields as $field) {
            SelfDeclerationBasefield::updateOrCreate(
                [
                    'scheme_id' => 0,
                    'tab_code'  => $tabCode,
                    'field_name'  => $field['field_name'],
                ],
                [
                    'section_level_id' => null,
                    'field_type'       => $field['field_type'],
                    'level_name'       => $field['level_name'],
                    'field_id'       => $field['field_name'],
                    'options'          => null,
                    'db_colunm'        => $field['db_colunm'],
                    'validation_rule'  => $field['validation_rule'],
                    'regex'            => null,
                    'is_multiple'      => false,
                    'field_position'   => $field['field_position'],
                    'is_active'        => true,
                ]
            );
        }

        $this->command->info('Self Declaration basefields inserted/updated successfully');
    }
}
