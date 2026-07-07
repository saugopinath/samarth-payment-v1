<?php

namespace Database\Seeders;

use App\Models\Codemaster;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OpTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codemasterParents = array(
            array(
                "name" => "OP TYPE",
                "short_name" => "op_type",
                "code" => "210",
            ),
            array(
                "name" => "DYNAMIC OP TYPE",
                "short_name" => "dynamic_op_type",
                "code" => "510",
            ),
        );
        foreach ($codemasterParents as $codemasterParent_item) {
            Codemaster::updateOrCreate([
                'name'     => strtoupper($codemasterParent_item['name']),
                'code'     => $codemasterParent_item['code'],
                'short_name'     => $codemasterParent_item['short_name'],
            ]);
        }
        $codemasterChilds = array(
            array(
                "name" => "Application Accepted",
                "short_name" => "application_accepted",
                "parent_short_code" => "op_type",
                "code" => "2101",
            ),
            array(
                "name" => "Application Rejected",
                "short_name" => "application_rejected",
                "parent_short_code" => "op_type",
                "code" => "2102",
            ),
            array(
                "name" => "Application Verify",
                "short_name" => "application_verify",
                "parent_short_code" => "op_type",
                "code" => "2103",
            ),
            array(
                "name" => "Application Approved",
                "short_name" => "application_approved",
                "parent_short_code" => "op_type",
                "code" => "2104",
            ),
            array(
                "name" => "Application Reverted",
                "short_name" => "application_reverted",
                "parent_short_code" => "op_type",
                "code" => "2105",
            ),
            array(
                "name" => "Application Partial",
                "short_name" => "application_partial",
                "parent_short_code" => "op_type",
                "code" => "2106",
            ),
        );
        foreach ($codemasterChilds as $codemasterChild_item) {
            Codemaster::updateOrCreate([
                'name' => strtoupper($codemasterChild_item['name']),
                'code' => $codemasterChild_item['code'],
                'parent_short_code' => $codemasterChild_item['parent_short_code'],
                'short_name'     => $codemasterChild_item['short_name'],
                'parent_id'   => Codemaster::where('short_name', $codemasterChild_item['parent_short_code'])->firstOrFail()->id,
            ]);
        }
    }
}
