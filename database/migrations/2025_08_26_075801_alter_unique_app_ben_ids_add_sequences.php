<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE pension.unique_app_ben_ids 
            ALTER COLUMN application_id DROP DEFAULT;
        ");

        DB::statement("CREATE SEQUENCE IF NOT EXISTS pension_unique_app_ben_ids_application_id_seq START 150000000;");
        DB::statement("CREATE SEQUENCE IF NOT EXISTS pension_unique_app_ben_ids_beneficiary_id_seq START 700000000;");
        DB::statement("
            ALTER TABLE pension.unique_app_ben_ids 
            ALTER COLUMN application_id 
            SET DEFAULT nextval('pension_unique_app_ben_ids_application_id_seq');
        ");

        DB::statement("
            ALTER TABLE pension.unique_app_ben_ids 
            ALTER COLUMN beneficiary_id 
            SET DEFAULT nextval('pension_unique_app_ben_ids_beneficiary_id_seq');
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        DB::statement("
            ALTER TABLE pension.unique_app_ben_ids 
            ALTER COLUMN application_id DROP DEFAULT;
        ");
        DB::statement("
            ALTER TABLE pension.unique_app_ben_ids 
            ALTER COLUMN beneficiary_id DROP DEFAULT;
        ");

        DB::statement("DROP SEQUENCE IF EXISTS pension_unique_app_ben_ids_application_id_seq;");
        DB::statement("DROP SEQUENCE IF EXISTS pension_unique_app_ben_ids_beneficiary_id_seq;");
    }
};
