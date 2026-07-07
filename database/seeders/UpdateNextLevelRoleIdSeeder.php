<?php

namespace Database\Seeders;

use App\Models\Codemaster;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateNextLevelRoleIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $codemasterChilds = array(
            array(
                "name" => "NEXT LEVEL ROLE ID APPROVED",
                "short_name" => "next_level_role_id_approved",
                "parent_short_code" => "next_level_role_id",
                "code" => "0",
            ),
            array(
                "name" => "NEXT LEVEL ROLE ID REJECTED",
                "short_name" => "next_level_role_id_rejected",
                "parent_short_code" => "next_level_role_id",
                "code" => "-1",
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
