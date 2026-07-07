<?php

namespace Database\Seeders\Bank;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bankmaster;
use App\Models\State;
class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banklist = array(
            array('name'=>'KOTAK MAHINDRA BANK LIMITED','ifsc'=>'KKBK'),
array('name'=>'RESERVE BANK OF INDIA','ifsc'=>'RBIS'),
array('name'=>'INDIAN BANK','ifsc'=>'IDIB'),
array('name'=>'CENTRAL BANK OF INDIA','ifsc'=>'CBIN'),
array('name'=>'ESAF SMALL FINANCE BANK LIMITED','ifsc'=>'ESMF'),
array('name'=>'UTKARSH SMALL FINANCE BANK','ifsc'=>'UTKS'),
array('name'=>'JAMMU AND KASHMIR BANK LIMITED','ifsc'=>'JAKA'),
array('name'=>'CITI BANK','ifsc'=>'CITI'),
array('name'=>'PUNJAB AND SIND BANK','ifsc'=>'PSIB'),
array('name'=>'THE WEST BENGAL STATE COOPERATIVE BANK','ifsc'=>'WBSC'),
array('name'=>'KARUR VYSYA BANK','ifsc'=>'KVBL'),
array('name'=>'KARNATAKA BANK LIMITED','ifsc'=>'KARB'),
array('name'=>'UCO BANK','ifsc'=>'UCBA'),
array('name'=>'PUNJAB NATIONAL BANK','ifsc'=>'PUNB'),
array('name'=>'NORTH EAST SMALL FINANCE BANK LIMITED','ifsc'=>'NESF'),
array('name'=>'BANK OF BARODA','ifsc'=>'BARB'),
array('name'=>'STANDARD CHARTERED BANK','ifsc'=>'SCBL'),
array('name'=>'BANK OF INDIA','ifsc'=>'BKID'),
array('name'=>'ICICI BANK LIMITED','ifsc'=>'ICIC'),
array('name'=>'BANDHAN BANK LIMITED','ifsc'=>'BDBL'),
array('name'=>'BANK OF MAHARASHTRA','ifsc'=>'MAHB'),
array('name'=>'CANARA BANK','ifsc'=>'CNRB'),
array('name'=>'UJJIVAN SMALL FINANCE BANK LIMITED','ifsc'=>'UJVN'),
array('name'=>'DEUTSCHE BANK','ifsc'=>'DEUT'),
array('name'=>'CITY UNION BANK LIMITED','ifsc'=>'CIUB'),
array('name'=>'JANA SMALL FINANCE BANK LTD','ifsc'=>'JSFB'),
array('name'=>'SOUTH INDIAN BANK','ifsc'=>'SIBL'),
array('name'=>'IDBI BANK','ifsc'=>'IBKL'),
array('name'=>'FINO PAYMENTS BANK','ifsc'=>'FINO'),
array('name'=>'UNION BANK OF INDIA','ifsc'=>'UBIN'),
array('name'=>'DHANALAKSHMI BANK','ifsc'=>'DLXB'),
array('name'=>'HSBC BANK','ifsc'=>'HSBC'),
array('name'=>'RBL BANK LIMITED','ifsc'=>'RATN'),
array('name'=>'CSB BANK LIMITED','ifsc'=>'CSBK'),
array('name'=>'IDFC FIRST BANK LTD','ifsc'=>'IDFB'),
array('name'=>'STATE BANK OF INDIA','ifsc'=>'SBIN'),
array('name'=>'BOMBAY MERCANTILE COOPERATIVE BANK LTD','ifsc'=>'BMCB'),
array('name'=>'DCB BANK LIMITED','ifsc'=>'DCBL'),
array('name'=>'TAMILNAD MERCANTILE BANK LIMITED','ifsc'=>'TMBL'),
array('name'=>'INDIAN OVERSEAS BANK','ifsc'=>'IOBA'),
array('name'=>'DBS BANK INDIA LIMITED','ifsc'=>'DBSS'),
array('name'=>'DURGAPUR STEEL PEOPLES CO-OPERATIVE BANK LTD','ifsc'=>'DURG'),
array('name'=>'FEDERAL BANK','ifsc'=>'FDRL'),
array('name'=>'HDFC BANK','ifsc'=>'HDFC'),
array('name'=>'YES BANK','ifsc'=>'YESB'),
array('name'=>'THE BURDWAN CENTRAL CO OPERATIVE BANK LTD','ifsc'=>'BUCB'),
array('name'=>'INDUSIND BANK','ifsc'=>'INDB'),
array('name'=>'AU SMALL FINANCE BANK LIMITED','ifsc'=>'AUBL'),
array('name'=>'UNITY SMALL FINANCE BANK LIMITED','ifsc'=>'UNBA'),
array('name'=>'AXIS BANK','ifsc'=>'UTIB'),
            
        );
        foreach ($banklist as $bank_item) {
            Bankmaster::create([
                'name'     => strtoupper($bank_item['name']),
                'short_name'     => $bank_item['ifsc'],
                'bank_code'     => $bank_item['ifsc']
            ]);
        }
    }
}
