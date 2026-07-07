<?php

namespace Database\Seeders\LGD;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\District;
use App\Models\State;
class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lg_wb_districts = array(
            array(
                "district_code" => "303",
                "ref_code" => "09",
                "district_name_english" => "24 PARAGANAS NORTH",
                "district_short_name" => "N24",
            ),
            array(
                "district_code" => "304",
                "ref_code" => "10",
                "district_name_english" => "24 PARAGANAS SOUTH",
                "district_short_name" => "S24",
            ),
            array(
                "district_code" => "664",
                "ref_code" => "21",
                "district_name_english" => "ALIPURDUAR",
                "district_short_name" => "APD",
            ),
            array(
                "district_code" => "305",
                "ref_code" => "17",
                "district_name_english" => "BANKURA",
                "district_short_name" => "BNR",
            ),
            array(
                "district_code" => "307",
                "ref_code" => "19",
                "district_name_english" => "BIRBHUM",
                "district_short_name" => "BIR",
            ),
            array(
                "district_code" => "308",
                "ref_code" => "01",
                "district_name_english" => "COOCHBEHAR",
                "district_short_name" => "CBH",
            ),
            array(
                "district_code" => "309",
                "ref_code" => "03",
                "district_name_english" => "DARJEELING",
                "district_short_name" => "DRJ",
            ),
            array(
                "district_code" => "310",
                "ref_code" => "05",
                "district_name_english" => "DINAJPUR DAKSHIN",
                "district_short_name" => "DPD",
            ),
            array(
                "district_code" => "311",
                "ref_code" => "04",
                "district_name_english" => "DINAJPUR UTTAR",
                "district_short_name" => "DPU",
            ),
            array(
                "district_code" => "312",
                "ref_code" => "13",
                "district_name_english" => "HOOGHLY",
                "district_short_name" => "HOO",
            ),
            array(
                "district_code" => "313",
                "ref_code" => "12",
                "district_name_english" => "HOWRAH",
                "district_short_name" => "HWR",
            ),
            array(
                "district_code" => "314",
                "ref_code" => "02",
                "district_name_english" => "JALPAIGURI",
                "district_short_name" => "JLP",
            ),
            array(
                "district_code" => "703",
                "ref_code" => "22",
                "district_name_english" => "JHARGRAM",
                "district_short_name" => "GRM",
            ),
            array(
                "district_code" => "702",
                "ref_code" => "23",
                "district_name_english" => "KALIMPONG",
                "district_short_name" => "MPN",
            ),
            array(
                "district_code" => "315",
                "ref_code" => "11",
                "district_name_english" => "KOLKATA",
                "district_short_name" => "KLK",
            ),
            array(
                "district_code" => "316",
                "ref_code" => "06",
                "district_name_english" => "MALDAH",
                "district_short_name" => "MLD",
            ),
            array(
                "district_code" => "317",
                "ref_code" => "14",
                "district_name_english" => "MEDINIPUR EAST",
                "district_short_name" => "MPE",
            ),
            array(
                "district_code" => "318",
                "ref_code" => "15",
                "district_name_english" => "MEDINIPUR WEST",
                "district_short_name" => "MPW",
            ),
            array(
                "district_code" => "319",
                "ref_code" => "07",
                "district_name_english" => "MURSHIDABAD",
                "district_short_name" => "MRS",
            ),
            array(
                "district_code" => "320",
                "ref_code" => "08",
                "district_name_english" => "NADIA",
                "district_short_name" => "NAD",
            ),
            array(
                "district_code" => "704",
                "ref_code" => "20",
                "district_name_english" => "PASCHIM BARDHAMAN",
                "district_short_name" => "MBR",
            ),
            array(
                "district_code" => "306",
                "ref_code" => "18",
                "district_name_english" => "PURBA BARDHAMAN",
                "district_short_name" => "BRD",
            ),
            array(
                "district_code" => "321",
                "ref_code" => "16",
                "district_name_english" => "PURULIA",
                "district_short_name" => "PRL",
            ),
        );

        foreach ($lg_wb_districts as $district) {
            District::create([
                'id'   => $district['district_code'],
                'ref_code'   => $district['ref_code'],
                'lgd_code'    => $district['district_code'],
                'name'       => strtoupper($district['district_name_english']),
                'short_name' => $district['district_short_name'],
                'state_id'   => State::where('lgd_code', '19')->firstOrFail()->id,
            ]);
        }
    }
}
