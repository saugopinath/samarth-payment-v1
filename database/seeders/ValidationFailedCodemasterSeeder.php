<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Codemaster;
class ValidationFailedCodemasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codemasterParents = array(
            array(
                "name" => "VALIDATION FAILED",
                "short_name" => "validation_failed",
                "code" => "18",
            ),
            array(
                "name" => "PAYMENT FAILED",
                "short_name" => "payment_failed",
                "code" => "19",
            ),
            array(
                "name" => "ALLOWED VALIDATION PARAMETERS",
                "short_name" => "allowed_validation_parameters",
                "code" => "20",
            ),
        );
        foreach ($codemasterParents as $codemasterParent_item) {
            Codemaster::create([
                'name'     => strtoupper($codemasterParent_item['name']),
                'code'     => $codemasterParent_item['code'],
                'short_name'     => $codemasterParent_item['short_name'],
            ]);
        }
        $codemasterChilds = array(
        
            array(
                "name" => "Name Validation Failed",
                "short_name" => "name_validation_failed",
                "parent_short_code" => "validation_failed",
                 "code" => "181",
            ),
            array(
                "name" => "Account Validation Failed",
                "short_name" => "account_validation_failed",
                "parent_short_code" => "validation_failed",
                 "code" => "182",
            ),
            array(
                "name" => "MAJOR MISMATCH",
                "short_name" => "major_mismatch",
                "parent_short_code" => "allowed_validation_parameters",
                 "code" => "201",
            ),
            array(
                "name" => "MINOR MISMATCH",
                "short_name" => "minor_mismatch",
                "parent_short_code" => "allowed_validation_parameters",
                 "code" => "202",
            ),
            array(
                "name" => "KEEP SAME",
                "short_name" => "keep_same",
                "parent_short_code" => "allowed_validation_parameters",
                 "code" => "203",
            ),
            array(
                "name" => "REJECT",
                "short_name" => "reject",
                "parent_short_code" => "allowed_validation_parameters",
                 "code" => "204",
            ),


        );
        foreach ($codemasterChilds as $codemasterChild_item) {
            Codemaster::create([
                'name' => strtoupper($codemasterChild_item['name']),
                'code' => $codemasterChild_item['code'],
                'parent_short_code' => $codemasterChild_item['parent_short_code'],
                'short_name'     => $codemasterChild_item['short_name'],
                'parent_id'   => Codemaster::where('short_name', $codemasterChild_item['parent_short_code'])->firstOrFail()->id,
            ]);
        }
    }
}
