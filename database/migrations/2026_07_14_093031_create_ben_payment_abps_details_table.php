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
        DB::statement('CREATE SCHEMA IF NOT EXISTS payment');
        
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];
        foreach ($schemeIds as $schemeItem) {
            DB::statement("DROP TABLE IF EXISTS payment.ben_payment_abps_details_{$schemeItem} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.ben_payment_abps_details CASCADE');

        DB::statement("CREATE TABLE IF NOT EXISTS payment.ben_payment_abps_details
(
    ben_id integer NOT NULL,
    scheme_id smallint NOT NULL,
    aadhar_no character(12) NOT NULL,
    is_clean smallint NOT NULL DEFAULT 1,
    CONSTRAINT ben_payment_abps_details_pkey PRIMARY KEY (ben_id, scheme_id, is_clean)
) PARTITION BY LIST (scheme_id);");
        DB::statement("
            ALTER TABLE payment.ben_payment_abps_details
            ADD CONSTRAINT fk_ben_payment_abps_details_scheme FOREIGN KEY (scheme_id)
            REFERENCES public.schemes (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");
         DB::statement("
         ALTER TABLE IF EXISTS payment.ben_payment_abps_details
    ADD CONSTRAINT fk_ben_payment_abps_details_ben_id_scheme_id FOREIGN KEY (ben_id,scheme_id)
    REFERENCES payment.ben_payment_details (ben_id,scheme_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;
        ");
           $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];
           $is_clean = [1,2,10];

       

            foreach ($schemeIds as $schemeItem) {
                DB::statement("                  
CREATE TABLE payment.ben_payment_abps_details_{$schemeItem} PARTITION OF payment.ben_payment_abps_details FOR VALUES IN ($schemeItem)
PARTITION BY LIST (is_clean);");
                 foreach ($is_clean as $is_clean_item) {
                   // $fy_suffix = str_replace('-', '_', $fin_year_item);
                    DB::statement("
CREATE TABLE payment.ben_payment_abps_details_clean_{$schemeItem}_{$is_clean_item} PARTITION OF payment.ben_payment_abps_details_{$schemeItem} FOR VALUES IN ($is_clean_item);");
            }

            DB::statement("
                CREATE TABLE payment.ben_payment_abps_details_{$schemeItem}_default
                PARTITION OF payment.ben_payment_abps_details_{$schemeItem}
                DEFAULT
            ");
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];
        foreach ($schemeIds as $schemeItem) {
            DB::statement("DROP TABLE IF EXISTS payment.ben_payment_abps_details_{$schemeItem} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.ben_payment_abps_details CASCADE');
    }
};
