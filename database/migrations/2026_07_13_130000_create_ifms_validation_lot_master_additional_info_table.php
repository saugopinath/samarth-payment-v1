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
        DB::statement('DROP TABLE IF EXISTS ifms.validation_lot_master_additional_info CASCADE');

        DB::statement("
            CREATE TABLE ifms.validation_lot_master_additional_info (
                lot_no int4 NOT NULL,
                scheme_id int4 NOT NULL,
                header_id character(2) ,
                originator_code character(11)  NOT NULL,
                responder_code character(11)  NOT NULL,
                file_ref_no character(10)  NOT NULL,
                total_record numeric(6,0) NOT NULL,
                input_file_name character varying(50)  NOT NULL,
                filler character(452),
                status integer DEFAULT 0,
                ack_status character(3),
                legacy_validation bool DEFAULT false NULL,
                CONSTRAINT pk_av_lot_ifms PRIMARY KEY (scheme_id, lot_no)
            )
            PARTITION BY LIST (scheme_id);
        ");

        DB::statement("
            ALTER TABLE ifms.validation_lot_master_additional_info 
            ADD CONSTRAINT fk_validation_lot_master_additional_info_ifms FOREIGN KEY (lot_no,scheme_id) 
            REFERENCES payment.validation_lot_master(lot_no,scheme_id)
        ");

        $schemeIds = [1,2,3,5,6,7,8,9,10,11,13,17,19,20,21];

        foreach ($schemeIds as $schemeItem) {
            DB::statement("
                CREATE TABLE ifms.ivlmai_s{$schemeItem}
                PARTITION OF ifms.validation_lot_master_additional_info
                FOR VALUES IN ({$schemeItem})
            ");
        }

        DB::statement("
            CREATE TABLE ifms.ivlmai_default
            PARTITION OF ifms.validation_lot_master_additional_info
            DEFAULT
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS ifms.validation_lot_master_additional_info CASCADE');
    }
};
