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
            DB::statement("DROP TABLE IF EXISTS payment.bmps_fy_{$fy_suffix} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.bmps_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS payment.ben_monthwise_payment_status CASCADE');

        DB::statement("
            CREATE TABLE IF NOT EXISTS payment.ben_monthwise_payment_status
            (
                id serial,
                financial_year character varying(9) COLLATE pg_catalog.\"default\" NOT NULL,
                ben_id integer NOT NULL,
                scheme_id smallint NOT NULL,
                present_amt integer DEFAULT 0,
                present_count integer DEFAULT 0,
                apr_lot_no integer,
                apr_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                apr_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                apr_is_eligible boolean DEFAULT true,
                apr_eligible_amount integer DEFAULT 0,
                apr_payment_amount integer DEFAULT 0,
                may_lot_no integer,
                may_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                may_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                may_is_eligible boolean DEFAULT true,
                may_eligible_amount integer DEFAULT 0,
                may_payment_amount integer DEFAULT 0,
                jun_lot_no integer,
                jun_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                jun_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                jun_is_eligible boolean DEFAULT true,
                jun_eligible_amount integer DEFAULT 0,
                jun_payment_amount integer DEFAULT 0,
                jul_lot_no integer,
                jul_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                jul_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                jul_is_eligible boolean DEFAULT true,
                jul_eligible_amount integer DEFAULT 0,
                jul_payment_amount integer DEFAULT 0,
                aug_lot_no integer,
                aug_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                aug_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                aug_is_eligible boolean DEFAULT true,
                aug_eligible_amount integer DEFAULT 0,
                aug_payment_amount integer DEFAULT 0,
                sep_lot_no integer,
                sep_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                sep_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                sep_is_eligible boolean DEFAULT true,
                sep_eligible_amount integer DEFAULT 0,
                sep_payment_amount integer DEFAULT 0,
                oct_lot_no integer,
                oct_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                oct_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                oct_is_eligible boolean DEFAULT true,
                oct_eligible_amount integer DEFAULT 0,
                oct_payment_amount integer DEFAULT 0,
                nov_lot_no integer,
                nov_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                nov_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                nov_is_eligible boolean DEFAULT true,
                nov_eligible_amount integer DEFAULT 0,
                nov_payment_amount integer DEFAULT 0,
                dec_lot_no integer,
                dec_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                dec_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                dec_is_eligible boolean DEFAULT true,
                dec_eligible_amount integer DEFAULT 0,
                dec_payment_amount integer DEFAULT 0,
                jan_lot_no integer,
                jan_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                jan_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                jan_is_eligible boolean DEFAULT true,
                jan_eligible_amount integer DEFAULT 0,
                jan_payment_amount integer DEFAULT 0,
                feb_lot_no integer,
                feb_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                feb_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                feb_is_eligible boolean DEFAULT true,
                feb_eligible_amount integer DEFAULT 0,
                feb_payment_amount integer DEFAULT 0,
                mar_lot_no integer,
                mar_lot_type character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '5901'::bpchar,
                mar_lot_status character varying(10) COLLATE pg_catalog.\"default\" DEFAULT '52101'::bpchar,
                mar_is_eligible boolean DEFAULT true,
                mar_eligible_amount integer DEFAULT 0,
                mar_payment_amount integer DEFAULT 0,
                created_at timestamp(0) without time zone,
                updated_at timestamp(0) without time zone,
                deleted_at timestamp(0) without time zone,
                CONSTRAINT ben_monthwise_payment_status_pkey PRIMARY KEY (financial_year, scheme_id, ben_id)
            ) PARTITION BY LIST(financial_year);
        ");

        DB::statement("
            ALTER TABLE payment.ben_monthwise_payment_status
            ADD CONSTRAINT fk_ben_monthwise_payment_status_ben_id FOREIGN KEY (ben_id, scheme_id)
            REFERENCES payment.ben_payment_details (ben_id, scheme_id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");
     
     DB::statement("
            ALTER TABLE payment.ben_monthwise_payment_status
            ADD CONSTRAINT fk_payment_lot_master_year FOREIGN KEY (financial_year)
            REFERENCES public.financial_years (code) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("
                CREATE TABLE payment.bmps_fy_{$fy_suffix}
                PARTITION OF payment.ben_monthwise_payment_status
                FOR VALUES IN ('{$fin_year_item}')
                PARTITION BY LIST (scheme_id)
            ");

            foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    CREATE TABLE payment.bmps_fy_{$fy_suffix}_s{$schemeItem}
                    PARTITION OF payment.bmps_fy_{$fy_suffix}
                    FOR VALUES IN ({$schemeItem})
                ");
            }

            DB::statement("
                CREATE TABLE payment.bmps_fy_{$fy_suffix}_default
                PARTITION OF payment.bmps_fy_{$fy_suffix}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE payment.bmps_default
            PARTITION OF payment.ben_monthwise_payment_status
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
            DB::statement("DROP TABLE IF EXISTS payment.bmps_fy_{$fy_suffix} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.bmps_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS payment.ben_monthwise_payment_status CASCADE');
    }
};
