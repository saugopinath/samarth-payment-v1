<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\FinancialYear;
use App\Models\Scheme;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql_payment';
    
    /**
     * Disable transaction wrapping.
     *
     * @var bool
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS payment');
        $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("DROP TABLE IF EXISTS payment.bplm_fy_{$fy_suffix} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.bplm_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS payment.payment_lot_master CASCADE');
        DB::statement("
            CREATE TABLE IF NOT EXISTS payment.payment_lot_master
            (
                lot_no serial,
                lot_month character varying(3) COLLATE pg_catalog.\"default\",
                lot_year character varying(9) COLLATE pg_catalog.\"default\" NOT NULL,
                scheme_id smallint NOT NULL,
                payment_mode integer,
                lot_type_id integer,
                cur_status integer,
                file_name character varying(50) COLLATE pg_catalog.\"default\",
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                payment_push_date timestamp without time zone,
                response_receive_date timestamp without time zone,
                ben_count integer DEFAULT 0,
                success_count integer DEFAULT 0,
                failed_count integer DEFAULT 0,
                last_response_check_date timestamp with time zone,
                total_amount numeric(13,0) DEFAULT 0,
                success_amount numeric(13,0) DEFAULT 0,
                failed_amount numeric(13,0) DEFAULT 0,
                CONSTRAINT payment_lot_master_pkey PRIMARY KEY (lot_no, lot_year, scheme_id)
            ) PARTITION BY LIST(lot_year);
        ");

       DB::statement("
            ALTER TABLE payment.payment_lot_master
            ADD CONSTRAINT fk_payment_lot_master_cur_status FOREIGN KEY (cur_status)
            REFERENCES public.codemasters (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        DB::statement("
            ALTER TABLE payment.payment_lot_master
            ADD CONSTRAINT fk_payment_lot_master_lot_type_id FOREIGN KEY (lot_type_id)
            REFERENCES public.codemasters (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

       DB::statement("
            ALTER TABLE payment.payment_lot_master
            ADD CONSTRAINT fk_payment_lot_master_year FOREIGN KEY (lot_year)
            REFERENCES public.financial_years (code) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        DB::statement("
            ALTER TABLE payment.payment_lot_master
            ADD CONSTRAINT fk_payment_lot_master_payment_mode FOREIGN KEY (payment_mode)
            REFERENCES public.codemasters (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        DB::statement("
            ALTER TABLE payment.payment_lot_master
            ADD CONSTRAINT fk_payment_lot_master_scheme FOREIGN KEY (scheme_id)
            REFERENCES public.schemes (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("
                CREATE TABLE payment.bplm_fy_{$fy_suffix}
                PARTITION OF payment.payment_lot_master
                FOR VALUES IN ('{$fin_year_item}')
                PARTITION BY LIST (scheme_id)
            ");

            foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    CREATE TABLE payment.bplm_fy_{$fy_suffix}_s{$schemeItem}
                    PARTITION OF payment.bplm_fy_{$fy_suffix}
                    FOR VALUES IN ({$schemeItem})
                ");
            }

            DB::statement("
                CREATE TABLE payment.bplm_fy_{$fy_suffix}_default
                PARTITION OF payment.bplm_fy_{$fy_suffix}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE payment.bplm_default
            PARTITION OF payment.payment_lot_master
            DEFAULT
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("DROP TABLE IF EXISTS payment.bplm_fy_{$fy_suffix} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.bplm_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS payment.payment_lot_master CASCADE');
    }
};
