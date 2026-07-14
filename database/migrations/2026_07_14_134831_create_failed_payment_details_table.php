<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql_payment';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS payment');

        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];
        foreach ($schemeIds as $schemeItem) {
            DB::statement("DROP TABLE IF EXISTS payment.failed_payment_details_{$schemeItem} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.failed_payment_details_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS payment.failed_payment_details CASCADE');

        DB::statement('
            CREATE TABLE IF NOT EXISTS payment.failed_payment_details
            (
                id bigserial,
                lot_no integer NOT NULL,
                ben_id integer NOT NULL,
                scheme_id integer NOT NULL,
                validation_type character varying(3),
                status_code character varying(3),
                remarks text,
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                name_status character(1),
                name_status_code character varying(5),
                name_response character varying(200),
                matching_score smallint,
                CONSTRAINT faild_payment_details_pkey PRIMARY KEY (lot_no, scheme_id, ben_id)
            ) PARTITION BY LIST (scheme_id);
        ');

        foreach ($schemeIds as $schemeItem) {
            DB::statement("
                CREATE TABLE payment.failed_payment_details_{$schemeItem}
                PARTITION OF payment.failed_payment_details
                FOR VALUES IN ({$schemeItem})
            ");
        }

        DB::statement("
            CREATE TABLE payment.failed_payment_details_default
            PARTITION OF payment.failed_payment_details
            DEFAULT
        ");

        DB::unprepared('
            ALTER TABLE IF EXISTS payment.failed_payment_details
                ADD CONSTRAINT fk_failed_payment_details_ben_id FOREIGN KEY (ben_id, scheme_id)
                REFERENCES payment.ben_payment_details (ben_id, scheme_id) MATCH SIMPLE
                ON UPDATE NO ACTION
                ON DELETE NO ACTION;

            ALTER TABLE IF EXISTS payment.failed_payment_details
                ADD CONSTRAINT fk_failed_payment_details_validation_type FOREIGN KEY (validation_type)
                REFERENCES public.codemasters (code) MATCH SIMPLE
                ON UPDATE NO ACTION
                ON DELETE NO ACTION;

            ALTER TABLE IF EXISTS payment.failed_payment_details
                ADD CONSTRAINT fk_failed_payment_details_status_code FOREIGN KEY (status_code)
                REFERENCES public.codemasters (code) MATCH SIMPLE
                ON UPDATE NO ACTION
                ON DELETE NO ACTION;

            ALTER TABLE IF EXISTS payment.failed_payment_details
                ADD CONSTRAINT fk_failed_payment_details_name_status FOREIGN KEY (name_status)
                REFERENCES public.codemasters (code) MATCH SIMPLE
                ON UPDATE NO ACTION
                ON DELETE NO ACTION;

            ALTER TABLE IF EXISTS payment.failed_payment_details
                ADD CONSTRAINT fk_failed_payment_details_name_status_code FOREIGN KEY (name_status_code)
                REFERENCES public.codemasters (code) MATCH SIMPLE
                ON UPDATE NO ACTION
                ON DELETE NO ACTION;

            ALTER TABLE IF EXISTS payment.failed_payment_details
                ADD CONSTRAINT fk_failed_payment_details_scheme_id FOREIGN KEY (scheme_id)
                REFERENCES public.schemes (id) MATCH SIMPLE
                ON UPDATE NO ACTION
                ON DELETE NO ACTION;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];
        foreach ($schemeIds as $schemeItem) {
            DB::statement("DROP TABLE IF EXISTS payment.failed_payment_details_{$schemeItem} CASCADE");
        }
        DB::statement('DROP TABLE IF EXISTS payment.failed_payment_details_default CASCADE');
        DB::statement('DROP TABLE IF EXISTS payment.failed_payment_details CASCADE');
    }
};
