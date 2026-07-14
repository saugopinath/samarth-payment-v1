<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql_bandhan';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS bandhan');

        // Drop stranded table and partitions if they exist
        DB::statement('DROP TABLE IF EXISTS bandhan.transaction_lot_details CASCADE');

        // Create the sequence if it doesn't exist

        DB::statement("
            CREATE TABLE IF NOT EXISTS bandhan.transaction_lot_details
            (
                id serial,
                lot_no integer NOT NULL,
                lot_year character varying(9) COLLATE pg_catalog.\"default\" NOT NULL,
                scheme_id integer NOT NULL,
                ben_id integer NOT NULL,
                ben_name character varying(200) COLLATE pg_catalog.\"default\",
                ifsc character(11) COLLATE pg_catalog.\"default\",
                accno character(20) COLLATE pg_catalog.\"default\",
                amount_rs numeric(7,0) NOT NULL,
                status_code smallint,
                remarks text COLLATE pg_catalog.\"default\",
                CONSTRAINT pk_transaction_lot_details_lot_scheme PRIMARY KEY (lot_no, lot_year, scheme_id)
            ) PARTITION BY LIST (lot_year);
        ");
        DB::statement("
            ALTER TABLE bandhan.transaction_lot_details 
            ADD CONSTRAINT fk_transaction_lot_details_ben_id FOREIGN KEY (ben_id,scheme_id) 
            REFERENCES payment.ben_payment_details(ben_id,scheme_id)
        ");
       DB::statement("
            ALTER TABLE bandhan.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_lot_no_lot_year_scheme_id FOREIGN KEY (lot_no, lot_year, scheme_id)
            REFERENCES payment.payment_lot_master (lot_no, lot_year, scheme_id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");
        DB::statement("
            ALTER TABLE bandhan.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_scheme FOREIGN KEY (scheme_id)
            REFERENCES public.schemes (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");
        DB::statement("
            ALTER TABLE bandhan.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_status FOREIGN KEY (status_code)
            REFERENCES bandhan.codemasters (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");
   
         $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
         $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("
                CREATE TABLE bandhan.btld_fy_{$fy_suffix}
                PARTITION OF bandhan.transaction_lot_details
                FOR VALUES IN ('{$fin_year_item}')
                PARTITION BY LIST (scheme_id)
            ");

            foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    CREATE TABLE bandhan.btld_fy_{$fy_suffix}_s{$schemeItem}
                    PARTITION OF bandhan.btld_fy_{$fy_suffix}
                    FOR VALUES IN ({$schemeItem})
                ");
            }

            DB::statement("
                CREATE TABLE bandhan.btld_fy_{$fy_suffix}_default
                PARTITION OF bandhan.btld_fy_{$fy_suffix}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE bandhan.transaction_lot_details_default
            PARTITION OF bandhan.transaction_lot_details
            DEFAULT
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS bandhan.transaction_lot_details CASCADE');
    }
};
