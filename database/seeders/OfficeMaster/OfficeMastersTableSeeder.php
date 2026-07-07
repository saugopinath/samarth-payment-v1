<?php

namespace Database\Seeders\OfficeMaster;

use Illuminate\Database\Seeder;
use App\Models\OfficeMaster;

class OfficeMastersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

         $list = array(
            array(
                'name' => 'State Office',
                'address' => 'State Office',
                'zip' => NULL,
                'created_at' => '2025-07-10 14:20:24',
                'updated_at' => '2025-07-10 14:20:24',
                'office_type' => 151,
                'state_id' => 19,
                'district_id' => NULL,
                'block_id' => NULL,
                'subdivisions_id' => NULL,
                'municipalitiy_id' => NULL,
                'ward_id' => NULL,
                'panchayat_id' => NULL,
            ),
            array(
                'name' => 'Paschim Medinipur District Office',
                'address' => 'Paschim Medinipur District Office',
                'zip' => NULL,
                'created_at' => '2025-07-10 14:21:23',
                'updated_at' => '2025-07-10 14:21:23',
                'office_type' => 152,
                'state_id' => 19,
                'district_id' => 318,
                'block_id' => NULL,
                'subdivisions_id' => NULL,
                'municipalitiy_id' => NULL,
                'ward_id' => NULL,
                'panchayat_id' => NULL,
            ),
            array(
                'name' => 'Daspur -II Block Office',
                'address' => 'Daspur -II Block Office',
                'zip' => NULL,
                'created_at' => '2025-07-10 14:22:08',
                'updated_at' => '2025-07-10 14:22:08',
                'office_type' => 153,
                'state_id' => 19,
                'district_id' => 318,
                'block_id' => 2979,
                'subdivisions_id' => NULL,
                'municipalitiy_id' => NULL,
                'ward_id' => NULL,
                'panchayat_id' => NULL,
            ),
            array(
                'name' => 'Ghatal Sub Division Office',
                'address' => 'Ghatal Sub Division Office',
                'zip' => NULL,
                'created_at' => '2025-07-10 14:22:40',
                'updated_at' => '2025-07-10 14:22:40',
                'office_type' => 154,
                'state_id' => 19,
                'district_id' => 318,
                'block_id' => NULL,
                'subdivisions_id' => 34401,
                'municipalitiy_id' => NULL,
                'ward_id' => NULL,
                'panchayat_id' => NULL,
            )
            
        );
        foreach ($list as $item) {
            OfficeMaster::create([
                'name' => strtoupper($item['name']),
                'address' => $item['address'],
                'zip' => $item['zip'],
                'created_at' =>  $item['created_at'],
                'updated_at' =>  $item['updated_at'],
                'office_type_id' =>  $item['office_type'],
                'state_id' =>  $item['state_id'],
                'district_id' =>  $item['district_id'],
                'block_id' => $item['block_id'],
                'subdivision_id' =>  $item['subdivisions_id'],
                'municipalitiy_id' =>  $item['municipalitiy_id'],
                'ward_id' =>  $item['ward_id'],
                'panchayat_id' =>  $item['panchayat_id'],
            ]);
        }
    }
        
      
        
        
    
}