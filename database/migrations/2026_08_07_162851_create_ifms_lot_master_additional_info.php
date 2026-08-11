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
    protected $connection = 'pgsql_ifms';
    
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
        DB::statement('CREATE SCHEMA IF NOT EXISTS ifms');
        DB::statement('DROP TABLE IF EXISTS ifms.lot_master_additional_info CASCADE');

        DB::statement("
            CREATE TABLE IF NOT EXISTS ifms.lot_master_additional_info (
                id serial,
                lot_no integer NOT NULL,
                scheme_id integer NOT NULL,
                lot_year character varying(9) NOT NULL,
                debit_reference character(20),
                tran_date character(8),
                agency_dr_ref character(6),
                debit_narration character varying(50),
                CONSTRAINT ifms_lot_master_additional_info_pkey PRIMARY KEY (lot_no, lot_year, scheme_id)
            ) PARTITION BY LIST (lot_year);
        ");

        DB::statement("
            ALTER TABLE ifms.lot_master_additional_info
            ADD CONSTRAINT fk_ifms_lot_master_additional_info_year FOREIGN KEY (lot_year)
            REFERENCES public.financial_years (code) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

        DB::statement("
            ALTER TABLE ifms.lot_master_additional_info
            ADD CONSTRAINT fk_ifms_lot_master_additional_info_lot_no_scheme_id FOREIGN KEY (lot_no, lot_year, scheme_id)
            REFERENCES payment.payment_lot_master (lot_no, lot_year, scheme_id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");
        
        $finyears = ['2020-2021','2022-2023','2023-2024','2024-2025','2025-2026','2026-2027','2027-2028','2028-2029','2029-2030','2030-2031','2031-2032','2032-2033'];
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($finyears as $fin_year_item) {
            $fy_suffix = str_replace('-', '_', $fin_year_item);
            DB::statement("
                CREATE TABLE ifms.ilmai_fy_{$fy_suffix}
                PARTITION OF ifms.lot_master_additional_info
                FOR VALUES IN ('{$fin_year_item}')
                PARTITION BY LIST (scheme_id)
            ");

            foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    CREATE TABLE ifms.ilmai_fy_{$fy_suffix}_s{$schemeItem}
                    PARTITION OF ifms.ilmai_fy_{$fy_suffix}
                    FOR VALUES IN ({$schemeItem})
                ");
            }

            DB::statement("
                CREATE TABLE ifms.ilmai_fy_{$fy_suffix}_default
                PARTITION OF ifms.ilmai_fy_{$fy_suffix}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE ifms.ilmai_default
            PARTITION OF ifms.lot_master_additional_info
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
            DB::statement("DROP TABLE IF EXISTS ifms.ilmai_fy_{$fy_suffix} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS ifms.ilmai_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS ifms.lot_master_additional_info CASCADE');
    }
};
