<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncompletTypeModelMapping;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class IncompletTypeModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $incompletTypeModelMapping = array(

            array(
                "table_column" => "NO AADHAAR NUMBER",
                "incomplet_type_code" => "141",
                "model" => "BeneficiaryAadhaar",
            ),
            array(
                "table_column" => "NO MOBILE NUMBER",
                "incomplet_type_code" => "142",
                "model" => "BeneficiaryPersonal,FaultyBeneficiaryPersonal",
            ),
            array(
                "table_column" => "NAME VALIDATION  FAILED IN BANK",
                "incomplet_type_code" => "145",
                "model" => "FailedPaymentDetails",
            ),
            array(
                "table_column" => "ACCOUNT NUMBER VALIDATION  FAILED IN BANK",
                "incomplet_type_code" => "146",
                "model" => "FailedPaymentDetails",
            ),
            array(
                "table_column" => "DUPLICATE AADHAAR NUMBER",
                "incomplet_type_code" => "149",
                "model" => "BeneficiaryAadhaar",
            ),
            array(
                "table_column" => "DUPLICATE MOBILE NUMBER",
                "incomplet_type_code" => "1410",
                "model" => "BeneficiaryPersonal",
            ),
            array(
                "table_column" => "DUPLICATE BANK ACCOUNT NUMBER",
                "incomplet_type_code" => "1411",
                "model" => "BeneficiaryBank,FaultyBeneficiaryBank,BenPaymentDetails",
            ),
            array(
                "table_column" => "Minor Mismatch(40% - 89%)",
                "incomplet_type_code" => "1412",
                "model" => "FailedPaymentDetails",
            ),
            array(
                "table_column" => "Minor Mismatch(90% - 100%)",
                "incomplet_type_code" => "1413",
                "model" => "FailedPaymentDetails",
            ),
            array(
                "table_column" => "PDS Mismatch",
                "incomplet_type_code" => "1414",
                "model" => "BeneficiaryAadhaar",
            ),


        );
        foreach ($incompletTypeModelMapping as $incompletTypeModelMapping_item) {
            IncompletTypeModelMapping::updateOrCreate([
                'incomplet_type_code' => strtoupper($incompletTypeModelMapping_item['incomplet_type_code']),
                'table_column' => $incompletTypeModelMapping_item['table_column'],
                'model_name' => $incompletTypeModelMapping_item['model'],
            ]);
        }
    }
}
