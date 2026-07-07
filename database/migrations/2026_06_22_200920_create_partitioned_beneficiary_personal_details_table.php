<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create sequence
        DB::statement("
            CREATE SEQUENCE IF NOT EXISTS pension.beneficiary_personal_details_id_seq;
        ");

        // Create parent partitioned table
        DB::statement("
            CREATE TABLE pension.beneficiary_personal_details (
                scheme_id BIGINT NOT NULL,
                application_id BIGINT NOT NULL,
                beneficiary_id BIGINT,
                next_level_role_id INTEGER,
                application_date DATE,
                ds_date DATE,
                dob DATE,
                beneficiary_name VARCHAR(150),
                ben_mother_name VARCHAR(150),
                ben_father_name VARCHAR(150),
                ben_spouse_name VARCHAR(150),
                email VARCHAR(150),
                application_type VARCHAR(50),
                ds_registration_no VARCHAR(100),
                marital_status INTEGER,
                caste INTEGER,
                caste_cer_no VARCHAR(100),
                is_final SMALLINT NOT NULL DEFAULT 0,
                created_by_dist_code INTEGER,
                created_by_local_body_code INTEGER,
                created_by INTEGER,
                updated_by INTEGER,
                other_details JSONB,
                created_at TIMESTAMP(0),
                updated_at TIMESTAMP(0),
                is_clean SMALLINT DEFAULT 1,
                CONSTRAINT beneficiary_personal_details_pkey
                    PRIMARY KEY (application_id, scheme_id, is_clean),
                CONSTRAINT beneficiary_personal_details_application_unique
                    UNIQUE (application_id, scheme_id, is_clean)
            )
            PARTITION BY LIST (scheme_id)
        ");
        DB::statement("
    ALTER TABLE pension.beneficiary_personal_details
    ADD CONSTRAINT fk_bpd_application_id
    FOREIGN KEY (application_id)
    REFERENCES pension.unique_app_ben_ids(application_id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_personal_details
    ADD CONSTRAINT fk_bpd_beneficiary_id
    FOREIGN KEY (beneficiary_id)
    REFERENCES pension.unique_app_ben_ids(beneficiary_id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_personal_details
    ADD CONSTRAINT fk_bpd_scheme_id
    FOREIGN KEY (scheme_id)
    REFERENCES public.schemes(id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_personal_details
    ADD CONSTRAINT fk_bpd_next_level_role_id
    FOREIGN KEY (next_level_role_id)
    REFERENCES public.codemasters(id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_personal_details
    ADD CONSTRAINT fk_bpd_marital_status
    FOREIGN KEY (marital_status)
    REFERENCES public.codemasters(id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_personal_details
    ADD CONSTRAINT fk_bpd_caste
    FOREIGN KEY (caste)
    REFERENCES public.codemasters(id)
");

        $schemeIds = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 13, 17, 19, 20];
        $isCleans = [1, 2, 10];

        foreach ($schemeIds as $schemeId) {

            DB::statement("
                CREATE TABLE pension.bpd_s{$schemeId}
                PARTITION OF pension.beneficiary_personal_details
                FOR VALUES IN ({$schemeId})
                PARTITION BY LIST (is_clean)
            ");

            foreach ($isCleans as $isClean) {
                DB::statement("
                    CREATE TABLE pension.bpd_s{$schemeId}_c{$isClean}
                    PARTITION OF pension.bpd_s{$schemeId}
                    FOR VALUES IN ({$isClean})
                ");
            }

            DB::statement("
                CREATE TABLE pension.bpd_s{$schemeId}_default
                PARTITION OF pension.bpd_s{$schemeId}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE pension.bpd_default
            PARTITION OF pension.beneficiary_personal_details
            DEFAULT
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP TABLE IF EXISTS pension.beneficiary_personal_details CASCADE
        ");

        DB::statement("
            DROP SEQUENCE IF EXISTS pension.beneficiary_personal_details_id_seq
        ");
    }
};