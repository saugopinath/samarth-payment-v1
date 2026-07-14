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
    protected $connection = 'pgsql_payment';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS payment');
        DB::statement('DROP TABLE IF EXISTS payment.ben_payment_details CASCADE');

        DB::statement('
            CREATE TABLE IF NOT EXISTS payment.ben_payment_details
            (
                ben_id integer NOT NULL,
                ben_name character varying(300) COLLATE pg_catalog."default" NOT NULL,
                scheme_id smallint NOT NULL,
                last_accno character varying(50) COLLATE pg_catalog."default",
                last_ifsc character varying(11) COLLATE pg_catalog."default",
                npci_bank_code character(4) COLLATE pg_catalog."default",
                aadhar_no character(12) COLLATE pg_catalog."default",
                ben_status integer NOT NULL,
                last_acc_validated integer DEFAULT 0,
                last_acc_validated_reason jsonb,
                last_aadhar_validated integer DEFAULT 0,
                last_aadhar_validated_reason jsonb,
                caste integer,
                gender integer,
                mobile_no character varying(10) COLLATE pg_catalog."default",
                created_by_dist_code integer NOT NULL,
                created_by_sdo_code integer NOT NULL,
                created_by_block_code integer NOT NULL,
                dist_code integer,
                rural_urban_id smallint,
                block_code integer,
                municipality_code integer,
                gp_code integer,
                ward_code integer,
                created_at timestamp(0) without time zone,
                updated_at timestamp(0) without time zone,
                deleted_at timestamp(0) without time zone,
                applied_at timestamp(0) without time zone,
                approval_at timestamp(0) without time zone,
                rejected_at timestamp(0) without time zone,
                is_eligible boolean DEFAULT true,
                non_eligible_reason jsonb,
                is_rejected smallint DEFAULT \'0\'::smallint,
                rejection_cause jsonb,
                CONSTRAINT ben_payment_details_pkey PRIMARY KEY (ben_id, scheme_id)
            ) PARTITION BY LIST (scheme_id);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS payment.ben_payment_details CASCADE');
    }
};
