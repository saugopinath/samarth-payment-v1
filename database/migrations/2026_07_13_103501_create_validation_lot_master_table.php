<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS payment');
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];
        foreach ($schemeIds as $schemeItem) {
            DB::statement("DROP TABLE IF EXISTS payment.vlm_s{$schemeItem} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.vlm_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS payment.validation_lot_master CASCADE');

        DB::statement("
            CREATE TABLE IF NOT EXISTS payment.validation_lot_master
            (
                lot_no serial,
                scheme_id smallint NOT NULL,
                validation_mode character varying(10),
                cur_status integer,
                file_name character varying(50) COLLATE pg_catalog.\"default\",
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                validation_push_date timestamp without time zone,
                response_receive_date timestamp without time zone,
                ben_count integer DEFAULT 0,
                success_count integer DEFAULT 0,
                failed_count integer DEFAULT 0,
                last_response_check_date timestamp with time zone,
                CONSTRAINT validation_lot_master_pkey PRIMARY KEY (lot_no, scheme_id)
            ) PARTITION BY LIST (scheme_id);
        ");

        DB::statement("
            ALTER TABLE payment.validation_lot_master
            ADD CONSTRAINT fk_validation_lot_master_cur_status FOREIGN KEY (cur_status)
            REFERENCES public.codemasters (id) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");

       

       

        DB::statement("
            ALTER TABLE payment.validation_lot_master
            ADD CONSTRAINT fk_validation_lot_master_validation_mode_id FOREIGN KEY (validation_mode)
            REFERENCES public.codemasters (code) MATCH SIMPLE
            ON UPDATE NO ACTION
            ON DELETE NO ACTION
        ");



         $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($schemeIds as $schemeItem) {
            DB::statement("
                CREATE TABLE payment.vlm_s{$schemeItem}
                PARTITION OF payment.validation_lot_master
                FOR VALUES IN ({$schemeItem})
            ");
        }

        DB::statement("
            CREATE TABLE payment.vlm_default
            PARTITION OF payment.validation_lot_master
            DEFAULT
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];
        foreach ($schemeIds as $schemeItem) {
            DB::statement("DROP TABLE IF EXISTS payment.vlm_s{$schemeItem} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.vlm_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS payment.validation_lot_master CASCADE');
    }
};
