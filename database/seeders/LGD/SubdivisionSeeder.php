<?php

namespace Database\Seeders\LGD;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subdivision;
use App\Models\District;
class SubdivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wb_subdivisions = array(

            array('ref_code'=>'33701','name'=>'Bongaon','district_lgd_code'=>'303'),
array('ref_code'=>'33702','name'=>'Barasat','district_lgd_code'=>'303'),
array('ref_code'=>'99998','name'=>'Bidhannagar','district_lgd_code'=>'303'),
array('ref_code'=>'33704','name'=>'Basirhat','district_lgd_code'=>'303'),
array('ref_code'=>'33703','name'=>'Barrackpore','district_lgd_code'=>'303'),
array('ref_code'=>'34304','name'=>'Diamond Harbour','district_lgd_code'=>'304'),
array('ref_code'=>'34301','name'=>'Alipore','district_lgd_code'=>'304'),
array('ref_code'=>'34302','name'=>'Baruipur','district_lgd_code'=>'304'),
array('ref_code'=>'33901','name'=>'Bankura','district_lgd_code'=>'305'),
array('ref_code'=>'33903','name'=>'Bishnupur','district_lgd_code'=>'305'),
array('ref_code'=>'33502','name'=>'Barddhaman Dakshin','district_lgd_code'=>'306'),
array('ref_code'=>'33501','name'=>'Barddhaman Uttar','district_lgd_code'=>'306'),
array('ref_code'=>'33504','name'=>'Katwa','district_lgd_code'=>'306'),
array('ref_code'=>'33503','name'=>'Kalna','district_lgd_code'=>'306'),
array('ref_code'=>'33401','name'=>'Bolpur','district_lgd_code'=>'307'),
array('ref_code'=>'33402','name'=>'Suri','district_lgd_code'=>'307'),
array('ref_code'=>'33403','name'=>'Rampurhat','district_lgd_code'=>'307'),
array('ref_code'=>'32903','name'=>'Mathabhanga','district_lgd_code'=>'308'),
array('ref_code'=>'32901','name'=>'Cooch Behar','district_lgd_code'=>'308'),
array('ref_code'=>'32902','name'=>'Dinhata','district_lgd_code'=>'308'),
array('ref_code'=>'32905','name'=>'Tufanganj','district_lgd_code'=>'308'),
array('ref_code'=>'99997','name'=>'Haldibari','district_lgd_code'=>'308'),
array('ref_code'=>'32904','name'=>'Mekliganj','district_lgd_code'=>'308'),
array('ref_code'=>'32703','name'=>'Mirik','district_lgd_code'=>'309'),
array('ref_code'=>'32701','name'=>'Darjeeling','district_lgd_code'=>'309'),
array('ref_code'=>'32702','name'=>'Kurseong','district_lgd_code'=>'309'),
array('ref_code'=>'32704','name'=>'Siliguri','district_lgd_code'=>'309'),
array('ref_code'=>'33102','name'=>'Gangarampur','district_lgd_code'=>'310'),
array('ref_code'=>'33101','name'=>'Balurghat','district_lgd_code'=>'310'),
array('ref_code'=>'33002','name'=>'Raiganj','district_lgd_code'=>'311'),
array('ref_code'=>'33001','name'=>'Islampur','district_lgd_code'=>'311'),
array('ref_code'=>'33804','name'=>'Serampore','district_lgd_code'=>'312'),
array('ref_code'=>'33802','name'=>'Chandannagar','district_lgd_code'=>'312'),
array('ref_code'=>'33801','name'=>'Arambag','district_lgd_code'=>'312'),
array('ref_code'=>'33803','name'=>'Chinsurah (Hooghly Sadar)','district_lgd_code'=>'312'),
array('ref_code'=>'34101','name'=>'Howrah','district_lgd_code'=>'313'),
array('ref_code'=>'34102','name'=>'Uluberia','district_lgd_code'=>'313'),
array('ref_code'=>'32801','name'=>'Jalpaiguri','district_lgd_code'=>'314'),
array('ref_code'=>'32802','name'=>'Malbazar','district_lgd_code'=>'314'),
array('ref_code'=>'32803','name'=>'Dhupguri','district_lgd_code'=>'314'),
array('ref_code'=>'99999','name'=>'Kolkata','district_lgd_code'=>'315'),
array('ref_code'=>'33202','name'=>'Malda','district_lgd_code'=>'316'),
array('ref_code'=>'34501','name'=>'Egra','district_lgd_code'=>'317'),
array('ref_code'=>'34503','name'=>'Tamluk','district_lgd_code'=>'317'),
array('ref_code'=>'34504','name'=>'Contai','district_lgd_code'=>'317'),
array('ref_code'=>'99996','name'=>'Haldia','district_lgd_code'=>'317'),
array('ref_code'=>'34403','name'=>'Medinipur','district_lgd_code'=>'318'),
array('ref_code'=>'34402','name'=>'Kharagpur','district_lgd_code'=>'318'),
array('ref_code'=>'34401','name'=>'Ghatal','district_lgd_code'=>'318'),
array('ref_code'=>'33305','name'=>'Lalbagh','district_lgd_code'=>'319'),
array('ref_code'=>'33304','name'=>'Kandi','district_lgd_code'=>'319'),
array('ref_code'=>'33303','name'=>'Jangipur','district_lgd_code'=>'319'),
array('ref_code'=>'33301','name'=>'Baharampur','district_lgd_code'=>'319'),
array('ref_code'=>'33302','name'=>'Domkal','district_lgd_code'=>'319'),
array('ref_code'=>'33601','name'=>'Kalyani','district_lgd_code'=>'320'),
array('ref_code'=>'33603','name'=>'Ranaghat','district_lgd_code'=>'320'),
array('ref_code'=>'33602','name'=>'Krishnanagar','district_lgd_code'=>'320'),
array('ref_code'=>'34003','name'=>'Purulia','district_lgd_code'=>'321'),
array('ref_code'=>'34001','name'=>'Jhalda','district_lgd_code'=>'321'),
array('ref_code'=>'34004','name'=>'Raghunathpur','district_lgd_code'=>'321'),
array('ref_code'=>'66201','name'=>'Alipurduar','district_lgd_code'=>'664'),
array('ref_code'=>'70301','name'=>'Kalimpong','district_lgd_code'=>'702'),
array('ref_code'=>'70201','name'=>'Jhargram','district_lgd_code'=>'703'),
array('ref_code'=>'70402','name'=>'Durgapur','district_lgd_code'=>'704'),
array('ref_code'=>'70401','name'=>'Asansol','district_lgd_code'=>'704'),

           
        );

        foreach ($wb_subdivisions as $sdo) {
            $district_id = District::where('lgd_code', $sdo['district_lgd_code'])->firstOrFail()->id;
            Subdivision::create([
                'id' => $sdo['ref_code'],
                'ref_code' => $sdo['ref_code'],
                'name' => strtoupper($sdo['name']),
                'district_id' => $district_id,
            ]);
        }
    }
}
