<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Codemaster;

class RedressedStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codemasterParents = array(
            array(
                "name" => "Redressed Status",
                "short_name" => "redressed_status",
                "code" => "330",
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
                "name" => "Pending",
                "short_name" => "pending",
                "parent_short_code" => "redressed_status",
                "code" => "3301",
            ),
            array(
                "name" => "Marked but Approval Pending",
                "short_name" => "marked_but_approval_pending",
                "parent_short_code" => "redressed_status",
                "code" => "3302",
            ),
            array(
                "name" => "Marked and Approved but Yet not send to CMO",
                "short_name" => "marked_and_approved_but_yet_not_send_to_cmo",
                "parent_short_code" => "redressed_status",
                "code" => "3303",
            ),
            array(
                "name" => "Sent to Operator for New Entry",
                "short_name" => "sent_to_operator_for_new_entry",
                "parent_short_code" => "redressed_status",
                "code" => "3304",
            ),
            array(
                "name" => "Marked and Approved and Send to CMO",
                "short_name" => "marked_and_approved_and_send_to_cmo",
                "parent_short_code" => "redressed_status",
                "code" => "3305",
            ),
            array(
                "name" => "Grivance List with No BLock/Municipality LGD",
                "short_name" => "grivance_list_with_no_block/municipality_lgd",
                "parent_short_code" => "redressed_status",
                "code" => "3306",
            )
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
