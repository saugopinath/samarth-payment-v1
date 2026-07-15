<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql_payment';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schemeIds = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 13, 17, 19, 20, 21];
        $is_clean = [1, 2, 10];

        foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    ALTER TABLE IF EXISTS payment.ben_payment_abps_details_clean_{$schemeItem}_1
                    ADD CONSTRAINT ben_payment_abps_details_clean_scheme_id_aadhar_no_{$schemeItem}_1_key UNIQUE (scheme_id, aadhar_no, is_clean)
                ");
            
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schemeIds = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 13, 17, 19, 20, 21];
        $is_clean = [1, 2, 10];

        foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    ALTER TABLE IF EXISTS payment.ben_payment_abps_details_clean_{$schemeItem}_1
                    DROP CONSTRAINT IF EXISTS ben_payment_abps_details_clean_scheme_id_aadhar_no_{$schemeItem}_1_key
                ");
            
        }
    }
};

