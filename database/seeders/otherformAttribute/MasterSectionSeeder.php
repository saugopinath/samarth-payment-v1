<?php

namespace Database\Seeders\OtherformAttribute;

use App\Models\MasterSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $types = [
             ['scheme_id' => '20','section_name' => 'Land Details','section_short_name' =>'land_details'],
             ['scheme_id' => '20','section_name' => 'Family Details','section_short_name' =>'family_details'],
          
        ];
        foreach ($types as $type) {
            MasterSection::updateOrCreate(
                ['section_name' => $type['section_name']],     // UNIQUE KEY
                ['scheme_id' => $type['scheme_id'],
                'section_short_name' => $type['section_short_name']]
            );
        }
    }
}
