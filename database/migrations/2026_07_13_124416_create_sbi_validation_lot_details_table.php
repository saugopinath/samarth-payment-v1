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
        DB::statement('DROP TABLE IF EXISTS sbi.validation_lot_details CASCADE');

        DB::statement("
            CREATE TABLE IF NOT EXISTS sbi.validation_lot_details (
                id bigserial NOT NULL,
                lot_no int4 NOT NULL,
                scheme_id int4 NOT NULL,
                created_by_dist_code int4 NOT NULL,
                ben_id int4 NOT NULL,
                ben_name varchar(200) NOT NULL,
                accno varchar(20) NULL,
                ifsc varchar(20) NULL,
                status varchar(5) NULL,
                remarks text NULL,
                edited_status bpchar(1) DEFAULT '0'::bpchar NULL,
                response_status bpchar(1) DEFAULT 'N'::bpchar NOT NULL,
                status_code varchar(5) NULL,
                misc int2 NULL,
                av_ds_phase int2 NULL,
                av_account_status smallint NULL,
                name_status bpchar(1) NULL,
                name_status_code smallint NULL,
                name_response varchar(200) NULL,
                aaadhar_no bpchar(12) NULL,
                CONSTRAINT validation_lot_details_pkey_sbi PRIMARY KEY (scheme_id, id)
            )
            PARTITION BY LIST (scheme_id);
        ");

        DB::statement("
            ALTER TABLE sbi.validation_lot_details 
            ADD CONSTRAINT fk_validation_lot_details_av_account_status_sbi FOREIGN KEY (av_account_status) 
            REFERENCES sbi.codemasters(code)
        ");

        DB::statement("
            ALTER TABLE sbi.validation_lot_details 
            ADD CONSTRAINT fk_validation_lot_details_ben_id_sbi FOREIGN KEY (ben_id,scheme_id) 
            REFERENCES payment.ben_payment_details(ben_id,scheme_id)
        ");

        DB::statement("
            ALTER TABLE sbi.validation_lot_details 
            ADD CONSTRAINT fk_validation_lot_details_lot_scheme_sbi FOREIGN KEY (lot_no,scheme_id) 
            REFERENCES payment.validation_lot_master(lot_no,scheme_id)
        ");

        DB::statement("
            ALTER TABLE sbi.validation_lot_details 
            ADD CONSTRAINT fk_validation_lot_details_name_status_code_sbi FOREIGN KEY (name_status_code) 
            REFERENCES sbi.codemasters(code)
        ");

        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($schemeIds as $schemeItem) {
            DB::statement("
                CREATE TABLE sbi.svld_s{$schemeItem}
                PARTITION OF sbi.validation_lot_details
                FOR VALUES IN ({$schemeItem})
            ");
        }

        DB::statement("
            CREATE TABLE sbi.svld_default
            PARTITION OF sbi.validation_lot_details
            DEFAULT
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS sbi.validation_lot_details CASCADE');
    }
};
