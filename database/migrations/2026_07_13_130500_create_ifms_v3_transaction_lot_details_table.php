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
    protected $connection = 'pgsql_ifms_v3';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS ifms_v3');

        // Drop stranded table and partitions if they exist
        DB::statement('DROP TABLE IF EXISTS ifms_v3.transaction_lot_details CASCADE');

        DB::statement("
            CREATE TABLE IF NOT EXISTS ifms_v3.transaction_lot_details
            (
                id serial,
                lot_no integer NOT NULL,
                lot_year character varying(9) COLLATE pg_catalog.\"default\" NOT NULL,
                scheme_id integer NOT NULL,
                ben_id integer NOT NULL,
                accno varchar(20) NULL,
                ifsc varchar(20) NULL,
                amount_rs integer,
                order_no_date bpchar(100) NULL,
			    status bpchar(10) NULL,
			    reason varchar(150) NULL,
			    utr_no bpchar(50) NULL,
			    processed_flag int4 DEFAULT 0 NULL,
                push_to_ifms_status character varying(10),
                dotdone_status character varying(10),
                ack_status character varying(10),
                ref_no integer,
                wrongdata_status character varying(10),
                drn character(15),
                voucher_no bigint,
                voucher_date date,
                token_no integer,
                token_date date,
                ifms_wrongdata_count integer,
                rbi_failed_count integer,
                rbi_success_count integer,
                bill_share_status smallint DEFAULT 0,
                bill_generated_status smallint DEFAULT 0,
                drn_no character(15),
                reject_reason character varying(200),
                bill_status smallint,
                sanctionamount character varying(100),
                issueingauth character varying(500),
                sanctiondate character varying(50),
                sanctionnumber character varying(200),
                billno character varying(200),
                billdate character varying(200),
                wrong_data jsonb,
                CONSTRAINT pk_transaction_lot_details_lot_scheme_ifms_v3 PRIMARY KEY (lot_no, lot_year, scheme_id)
            ) PARTITION BY LIST (lot_year);
        ");

        DB::statement("
            ALTER TABLE ifms_v3.transaction_lot_details 
            ADD CONSTRAINT fk_transaction_lot_details_ben_id_ifms_v3 FOREIGN KEY (ben_id,scheme_id) 
            REFERENCES payment.ben_payment_details(ben_id,scheme_id)
        ");

        DB::statement("
            ALTER TABLE ifms_v3.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_lot_no_lot_year_scheme_id_ifms_v3 FOREIGN KEY (lot_no, lot_year, scheme_id)
            REFERENCES payment.payment_lot_master (lot_no, lot_year, scheme_id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        DB::statement("
            ALTER TABLE ifms_v3.transaction_lot_details
            ADD CONSTRAINT fk_transaction_lot_details_scheme_ifms_v3 FOREIGN KEY (scheme_id)
            REFERENCES public.schemes (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");



        $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("
                CREATE TABLE ifms_v3.iv3tld_fy_{$fy_suffix}
                PARTITION OF ifms_v3.transaction_lot_details
                FOR VALUES IN ('{$fin_year_item}')
                PARTITION BY LIST (scheme_id)
            ");

            foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    CREATE TABLE ifms_v3.iv3tld_fy_{$fy_suffix}_s{$schemeItem}
                    PARTITION OF ifms_v3.iv3tld_fy_{$fy_suffix}
                    FOR VALUES IN ({$schemeItem})
                ");
            }

            DB::statement("
                CREATE TABLE ifms_v3.iv3tld_fy_{$fy_suffix}_default
                PARTITION OF ifms_v3.iv3tld_fy_{$fy_suffix}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE ifms_v3.transaction_lot_details_default
            PARTITION OF ifms_v3.transaction_lot_details
            DEFAULT
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS ifms_v3.transaction_lot_details CASCADE');
    }
};
