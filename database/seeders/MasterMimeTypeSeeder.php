<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MasterMimeType;

class MasterMimeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mime_types = array(
            array(
                "extension_type" => "jpg",
                "mime_type" => "image/jpeg"
            ),
            array(
                "extension_type" => "jpeg",
                "mime_type" => "image/jpeg"
            ),
            array(
                "extension_type" => "png",
                "mime_type" => "image/png"
            ),
            array(
                "extension_type" => "pdf",
                "mime_type" => "application/pdf"
            ),
        );
        foreach ($mime_types as $mime_type) {
            MasterMimeType::create([
                'extension_type'     => $mime_type['extension_type'],
                'mime_type'     => $mime_type['mime_type'],
            ]);
        }
    }
}
