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
    protected $connection = 'pgsql_ifms';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS ifms');

        // Drop stranded table and partitions if they exist
        DB::statement('DROP TABLE IF EXISTS ifms.transaction_lot_details CASCADE');

        DB::statement("
            CREATE TABLE IF NOT EXISTS ifms.transaction_lot_details
            (
                id serial,
                lot_no integer NOT NULL,
                lot_year character varying(9) COLLATE pg_catalog.\"default\" NOT NULL,
                scheme_id integer NOT NULL,
                ben_id integer NOT NULL,
                accno varchar(20) NULL,
                ifsc varchar(20) NULL,
                amount_rs integer,
                payment_date date,
                order_no_date character varying(100),
                ref_no bigint,
                status character(10),
                reason character varying(150),
                utr_no character varying(50),
                processed_flag integer DEFAULT 0,
                push_to_ifms_status varchar(10) NULL,
                dotdone_status varchar(10) NULL,
                ack_status varchar(10) NULL,
                wrongdata_status varchar(10) NULL,
                drn bpchar(15) NULL,
                voucher_no int8 NULL,
                voucher_date date NULL,
                token_no int4 NULL,
                token_date date NULL,
                ifms_wrongdata_count int4 NULL,
                rbi_failed_count int4 NULL,
                rbi_success_count int4 NULL,
                CONSTRAINT pk_transaction_lot_details_lot_scheme_ifms PRIMARY KEY (lot_no, lot_year, scheme_id)
            ) PARTITION BY LIST (lot_year);
        ");

        DB::statement("
            ALTER TABLE ifms.transaction_lot_details 
            ADD CONSTRAINT fk_transaction_lot_details_ben_id_ifms FOREIGN KEY (ben_id,scheme_id) 
            REFERENCES payment.ben_payment_details(ben_id,scheme_id)
        ");

        DB::statement("
            ALTER TABLE ifms.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_lot_no_lot_year_scheme_id_ifms FOREIGN KEY (lot_no, lot_year, scheme_id)
            REFERENCES payment.payment_lot_master (lot_no, lot_year, scheme_id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        DB::statement("
            ALTER TABLE ifms.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_scheme_ifms FOREIGN KEY (scheme_id)
            REFERENCES public.schemes (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");



        $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("
                CREATE TABLE ifms.itld_fy_{$fy_suffix}
                PARTITION OF ifms.transaction_lot_details
                FOR VALUES IN ('{$fin_year_item}')
                PARTITION BY LIST (scheme_id)
            ");

            foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    CREATE TABLE ifms.itld_fy_{$fy_suffix}_s{$schemeItem}
                    PARTITION OF ifms.itld_fy_{$fy_suffix}
                    FOR VALUES IN ({$schemeItem})
                ");
            }

            DB::statement("
                CREATE TABLE ifms.itld_fy_{$fy_suffix}_default
                PARTITION OF ifms.itld_fy_{$fy_suffix}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE ifms.transaction_lot_details_default
            PARTITION OF ifms.transaction_lot_details
            DEFAULT
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS ifms.transaction_lot_details CASCADE');
    }
};
