<?php

namespace Database\Seeders;

use App\Models\Codemaster;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReactivateDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codemasterChilds = array(
            array(
                "name" => "Supporting Document For Jnmp",
                "short_name" => "sapporting_document_for_jnmp",
                "parent_short_code" => "ENCDETAILS",
                "code" => "1634",
            ),
            array(
                "name" => "Supporting Document For Reject Beneficiary",
                "short_name" => "sapporting_document_for_reject_beneficiary",
                "parent_short_code" => "ENCDETAILS",
                "code" => "1635",
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


