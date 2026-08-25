<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Codemaster;
class CodemasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codemasterParents = array(
            array(
                "name" => "OFFICE TYPE",
                "short_name" => "office_type",
                 "code" => "15",
            ),
            array(
                "name" => "CASTE",
                "short_name" => "caste",
                "code" => "17",
            ),
            array(
                "name" => "NEXT LEVEL ROLE ID",
                "short_name" => "next_level_role_id",
                "code" => "222",
            ),
            array(
                "name" => "Marital Status",
                "short_name" => "marital_status",
                "code" => "30",
            ),
            array(
                "name" => "Entry Type",
                "short_name" => "entry_type",
                "code" => "40",
            ),
            array(
                "name" => "GENDER",
                "short_name" => "gender",
                "code" => "555",
            ),
            array(
                "name" => "Disablity Type",
                "short_name" => "disablity_type",
                "code" => "60",
            ),
            array(
                "name" => "Ration Card Type",
                "short_name" => "ration_cat",
                "code" => "70",
            ),
            array(
                "name" => "Social Pension Body",
                "short_name" => "pension_body",
                "code" => "80",
            ),
            array(
                "name" => "Social Pension Catagory",
                "short_name" => "social_pension_cat",
                "code" => "90",
            ),
            array(
                "name" => "Religion",
                "short_name" => "religion",
                "code" => "110",
            ),
            array(
                "name" => "Rejection Cause",
                "short_name" => "rejection_cause",
                "code" => "120",
            ),
            array(
                "name" => "Relationship",
                "short_name" => "relationship",
                "code" => "130",
            ),
            array(
                "name" => "Incomplete Details",
                "short_name" => "incomplete_details",
                "code" => "140",
            ),
             array(
                "name" => "Payment Type",
                "short_name" => "payment_type",
                "code" => "50",
            ),
            array(
                "name" => "Lot Type",
                "short_name" => "lot_type",
                "code" => "59",
            ),
             array(
                "name" => "Payment Mode",
                "short_name" => "payment_mode",
                "code" => "520",
            ),
             array(
                "name" => "Payment Lot Status Common",
                "short_name" => "payment_lot_status_common",
                "code" => 'PLSC',
            ),
             array(
                "name" => "Payment Lot Status SBI",
                "short_name" => "payment_lot_status_sbi",
                "code" => 'PLSSBI',
            ),
             array(
                "name" => "Payment Lot Status IFMS",
                "short_name" => "payment_lot_status_ifms",
                "code" => 'PLSIFMS',
            ),
            array(
                "name" => "Payment Lot Status Bandhan",
                "short_name" => "payment_lot_status_bandhan",
                "code" => 'PLSBANDHAN',
            ),
             array(
                "name" => "Validation Lot Status",
                "short_name" => "validation_lot_status",
                "code" => "522",
            ),
             array(
                "name" => "Payment Lot Configuration",
                "short_name" => "payment_lot_configuration",
                "code" => "523",
            ),
            array(
                "name" => "Validation Lot Configuration",
                "short_name" => "validation_lot_configuration",
                "code" => "524",
            ),
             array(
                "name" => "Validation Mode",
                "short_name" => "validation_mode",
                "code" => "525",
            ),
             array(
                "name" => "SBI Status Code",
                "short_name" => "sbi_status_code",
                "code" => "526",
            ),
            array(
                "name" => "ENCLOSER DETAILS",
                "short_name" => "ENCDETAILS",
                "code" => "16",
            ),

        );
        $configParents = config('codemaster.parents');
        if (is_array($configParents)) {
            $codemasterParents = array_merge($codemasterParents, $configParents);
        }

        // Deduplicate parents by short_name just in case
        $uniqueParents = [];
        foreach ($codemasterParents as $parent) {
            $uniqueParents[$parent['short_name']] = $parent;
        }
        $codemasterParents = array_values($uniqueParents);

        foreach ($codemasterParents as $codemasterParent_item) {
            Codemaster::updateOrCreate(
                ['short_name' => $codemasterParent_item['short_name']],
                [
                    'name'     => strtoupper($codemasterParent_item['name']),
                    'code'     => $codemasterParent_item['code'],
                ]
            );
        }

        $codemasterChilds = array(
            array(
                "name" => "STATE OFFICE",
                "short_name" => "state_office",
                "parent_short_code" => "office_type",
                 "code" => "151",
            ),
            array(
                "name" => "DISTRICT OFFICE",
                "short_name" => "district_office",
                "parent_short_code" => "office_type",
                 "code" => "152",
            ),
            array(
                "name" => "BLOCK OFFICE",
                "short_name" => "block_office",
                "parent_short_code" => "office_type",
                 "code" => "153",
            ),
            array(
                "name" => "SUBDIVISION OFFICE",
                "short_name" => "subdivision_office",
                "parent_short_code" => "office_type",
                 "code" => "154",
            ),
            array(
                "name" => "MUNICIPALITY OFFICE",
                "short_name" => "municipality_office",
                "parent_short_code" => "office_type",
                 "code" => "155",
            ),
            array(
                "name" => "PANCHAYAT OFFICE",
                "short_name" => "panchayat_office",
                "parent_short_code" => "office_type",
                 "code" => "156",
            ),
            array(
                "name" => "WARD OFFICE",
                "short_name" => "ward_office",
                "parent_short_code" => "office_type",
                 "code" => "159",
            ),
        );
        
        $mainChilds = config('codemaster.childs');
        if (is_array($mainChilds)) {
            $codemasterChilds = array_merge($codemasterChilds, $mainChilds);
        }

        $paymentChilds = config('payment_lot.childs');
        if (is_array($paymentChilds)) {
            $codemasterChilds = array_merge($codemasterChilds, $paymentChilds);
        }
        $sbiChilds = config('sbi.payment_response_code');
        if (is_array($sbiChilds)) {
            $codemasterChilds = array_merge($codemasterChilds, $sbiChilds);
        }
        $validationChilds = config('validation.childs');
        if (is_array($validationChilds)) {
            $codemasterChilds = array_merge($codemasterChilds, $validationChilds);
        }
        $encDetails = config('encdetails.childs');
        if (is_array($encDetails)) {
            $codemasterChilds = array_merge($codemasterChilds, $encDetails);
        }
        // Deduplicate childs
        $uniqueChilds = [];
        foreach ($codemasterChilds as $child) {
            if (isset($child['short_name'])) {
                $uniqueChilds[$child['short_name']] = $child;
            }
        }
        $codemasterChilds = array_values($uniqueChilds);

        foreach ($codemasterChilds as $codemasterChild_item) {
            Codemaster::updateOrCreate(
                ['short_name' => $codemasterChild_item['short_name']],
                [
                    'name' => strtoupper($codemasterChild_item['name']),
                    'code' => $codemasterChild_item['code'],
                    'parent_short_code' => $codemasterChild_item['parent_short_code'],
                    'parent_id'   => Codemaster::where('short_name', $codemasterChild_item['parent_short_code'])->firstOrFail()->id,
                ]
            );
        }
    }
}
