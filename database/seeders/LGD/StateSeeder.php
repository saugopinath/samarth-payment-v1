<?php

namespace Database\Seeders\LGD;
use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lg_states = array(
            array(
                "state_code" => "1",
                "state_name_english" => "JAMMU AND KASHMIR",
                "state_ut" => "UT",
            ),
            array(
                "state_code" => "2",
                "state_name_english" => "HIMACHAL PRADESH",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "3",
                "state_name_english" => "PUNJAB",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "4",
                "state_name_english" => "CHANDIGARH",
                "state_ut" => "UT",
            ),
            array(
                "state_code" => "5",
                "state_name_english" => "UTTARAKHAND",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "6",
                "state_name_english" => "HARYANA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "7",
                "state_name_english" => "DELHI",
                "state_ut" => "UT",
            ),
            array(
                "state_code" => "8",
                "state_name_english" => "RAJASTHAN",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "9",
                "state_name_english" => "UTTAR PRADESH",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "10",
                "state_name_english" => "BIHAR",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "11",
                "state_name_english" => "SIKKIM",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "12",
                "state_name_english" => "ARUNACHAL PRADESH",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "13",
                "state_name_english" => "NAGALAND",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "14",
                "state_name_english" => "MANIPUR",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "15",
                "state_name_english" => "MIZORAM",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "16",
                "state_name_english" => "TRIPURA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "17",
                "state_name_english" => "MEGHALAYA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "18",
                "state_name_english" => "ASSAM",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "19",
                "state_name_english" => "WEST BENGAL",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "20",
                "state_name_english" => "JHARKHAND",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "21",
                "state_name_english" => "ODISHA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "22",
                "state_name_english" => "CHHATTISGARH",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "23",
                "state_name_english" => "MADHYA PRADESH",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "24",
                "state_name_english" => "GUJARAT",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "27",
                "state_name_english" => "MAHARASHTRA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "28",
                "state_name_english" => "ANDHRA PRADESH",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "29",
                "state_name_english" => "KARNATAKA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "30",
                "state_name_english" => "GOA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "31",
                "state_name_english" => "LAKSHADWEEP",
                "state_ut" => "UT",
            ),
            array(
                "state_code" => "32",
                "state_name_english" => "KERALA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "33",
                "state_name_english" => "TAMIL NADU",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "34",
                "state_name_english" => "PUDUCHERRY",
                "state_ut" => "UT",
            ),
            array(
                "state_code" => "35",
                "state_name_english" => "ANDAMAN AND NICOBAR ISLANDS",
                "state_ut" => "UT",
            ),
            array(
                "state_code" => "36",
                "state_name_english" => "TELANGANA",
                "state_ut" => "State",
            ),
            array(
                "state_code" => "37",
                "state_name_english" => "LADAKH",
                "state_ut" => "UT",
            ),
            array(
                "state_code" => "38",
                "state_name_english" => "THE DADRA AND NAGAR HAVELI AND DAMAN AND DIU",
                "state_ut" => "UT",
            ),
        );
        foreach ($lg_states as $state) {
            State::create([
                'ref_code' => $state['state_code'],
                'lgd_code'  => $state['state_code'],
                'name'     => strtoupper($state['state_name_english']),
                'state_ut' => $state['state_ut']
            ]);
        }
    }
}
