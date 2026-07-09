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
            CREATE TABLE payment.ben_payment_acc_details (
                ben_id integer NOT NULL,
                scheme_id smallint NOT NULL,
                last_accno character varying(50),
                last_ifsc character varying(11),
                is_clean smallint DEFAULT 1,
                npci_bank_code character(4),
                CONSTRAINT ben_payment_acc_details_is_clean_key UNIQUE (ben_id, scheme_id, last_accno, is_clean)
            )
            PARTITION BY LIST (scheme_id)
        ");
    DB::statement("
    ALTER TABLE payment.ben_payment_acc_details
    ADD CONSTRAINT fk_bpd_ben_id
    FOREIGN KEY (ben_id)
    REFERENCES payment.ben_payment_details(ben_id)
");

DB::statement("
    ALTER TABLE payment.ben_payment_acc_details
    ADD CONSTRAINT fk_bpd_scheme_id
    FOREIGN KEY (scheme_id)
    REFERENCES public.schemes(id)
");
DB::statement("
    ALTER TABLE payment.ben_payment_acc_details
    ADD CONSTRAINT fk_bpd_last_ifsc
    FOREIGN KEY (last_ifsc)
    REFERENCES public.ifsccodemasters(code)
");



         $schemeIds = Scheme::pluck('id')->where('is_active', 1)->toArray();
        $isCleans = [1, 2, 10];

        foreach ($schemeIds as $schemeId) {

            DB::statement("
                CREATE TABLE payment.bpad_s{$schemeId}
                PARTITION OF payment.ben_payment_acc_details
                FOR VALUES IN ({$schemeId})
                PARTITION BY LIST (is_clean)
            ");

            foreach ($isCleans as $isClean) {
                DB::statement("
                    CREATE TABLE payment.bpad_s{$schemeId}_c{$isClean}
                    PARTITION OF payment.bpad_s{$schemeId}
                    FOR VALUES IN ({$isClean})
                ");
            }

            DB::statement("
                CREATE TABLE payment.bpad_s{$schemeId}_default
                PARTITION OF payment.bpad_s{$schemeId}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE payment.bpad_default
            PARTITION OF payment.ben_payment_acc_details
            DEFAULT
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP TABLE IF EXISTS payment.ben_payment_acc_details CASCADE
        ");

       
    }
};