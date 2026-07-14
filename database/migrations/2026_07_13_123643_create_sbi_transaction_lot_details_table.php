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
    protected $connection = 'pgsql_sbi';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS sbi');

        // Drop stranded table and partitions if they exist
        DB::statement('DROP TABLE IF EXISTS sbi.transaction_lot_details CASCADE');

        DB::statement("
            CREATE TABLE IF NOT EXISTS sbi.transaction_lot_details
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
                debit_reference varchar(20) NOT NULL,
                credit_reference varchar(24) NULL,
                npci_user_id bpchar(7) NULL,
                npci_user_name varchar(20) NULL,
                agency_cr_ref varchar(50) NOT NULL,
                credit_payment_reference varchar(20) NULL,
                CONSTRAINT pk_transaction_lot_details_lot_scheme_sbi PRIMARY KEY (lot_no, lot_year, scheme_id)
            ) PARTITION BY LIST (lot_year);
        ");

        DB::statement("
            ALTER TABLE sbi.transaction_lot_details 
            ADD CONSTRAINT fk_transaction_lot_details_ben_id_sbi FOREIGN KEY (ben_id,scheme_id) 
            REFERENCES payment.ben_payment_details(ben_id,scheme_id)
        ");

        DB::statement("
            ALTER TABLE sbi.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_lot_no_lot_year_scheme_id_sbi FOREIGN KEY (lot_no, lot_year, scheme_id)
            REFERENCES payment.payment_lot_master (lot_no, lot_year, scheme_id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        DB::statement("
            ALTER TABLE sbi.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_scheme_sbi FOREIGN KEY (scheme_id)
            REFERENCES public.schemes (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        DB::statement("
            ALTER TABLE sbi.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_status_sbi FOREIGN KEY (status_code)
            REFERENCES sbi.codemasters (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("
                CREATE TABLE sbi.stld_fy_{$fy_suffix}
                PARTITION OF sbi.transaction_lot_details
                FOR VALUES IN ('{$fin_year_item}')
                PARTITION BY LIST (scheme_id)
            ");

            foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    CREATE TABLE sbi.stld_fy_{$fy_suffix}_s{$schemeItem}
                    PARTITION OF sbi.stld_fy_{$fy_suffix}
                    FOR VALUES IN ({$schemeItem})
                ");
            }

            DB::statement("
                CREATE TABLE sbi.stld_fy_{$fy_suffix}_default
                PARTITION OF sbi.stld_fy_{$fy_suffix}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE sbi.transaction_lot_details_default
            PARTITION OF sbi.transaction_lot_details
            DEFAULT
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS sbi.transaction_lot_details CASCADE');
    }
};
