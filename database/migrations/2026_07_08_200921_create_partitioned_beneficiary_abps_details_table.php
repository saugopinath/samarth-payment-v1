<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Scheme;
return new class extends Migration
{
    public function up(): void
    {
      

        // Create parent partitioned table
        DB::statement("
            CREATE TABLE payment.ben_payment_abps_details (
                 ben_id integer NOT NULL,
                 scheme_id smallint NOT NULL,
                 aadhar_no character(12) NOT NULL,
                 is_clean smallint DEFAULT 1
            )
            PARTITION BY LIST (scheme_id)");
    DB::statement("
    ALTER TABLE payment.ben_payment_abps_details
    ADD CONSTRAINT fk_bpd_ben_id
    FOREIGN KEY (ben_id)
    REFERENCES payment.ben_payment_details(ben_id)
");

DB::statement("
    ALTER TABLE payment.ben_payment_abps_details
    ADD CONSTRAINT fk_bpd_scheme_id
    FOREIGN KEY (scheme_id)
    REFERENCES public.schemes(id)
");




         $schemeIds = Scheme::pluck('id')->where('is_active', 1)->toArray();
        $isCleans = [1, 2, 10];

        foreach ($schemeIds as $schemeId) {

            DB::statement("
                CREATE TABLE payment.bpabps_s{$schemeId}
                PARTITION OF payment.ben_payment_abps_details
                FOR VALUES IN ({$schemeId})
                PARTITION BY LIST (is_clean)
            ");

            foreach ($isCleans as $isClean) {
                DB::statement("
                    CREATE TABLE payment.bpabps_s{$schemeId}_c{$isClean}
                    PARTITION OF payment.bpabps_s{$schemeId}
                    FOR VALUES IN ({$isClean})
                ");
            }

            DB::statement("
                CREATE TABLE payment.bpabps_s{$schemeId}_default
                PARTITION OF payment.bpabps_s{$schemeId}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE payment.bpabps_default
            PARTITION OF payment.ben_payment_abps_details
            DEFAULT
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP TABLE IF EXISTS payment.ben_payment_abps_details CASCADE
        ");

       
    }
};