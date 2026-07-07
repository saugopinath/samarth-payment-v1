<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create sequence
        DB::statement("
            CREATE SEQUENCE IF NOT EXISTS pension.beneficiary_contact_details_id_seq;
        ");

        // Create parent partitioned table
        DB::statement("
            CREATE TABLE pension.beneficiary_contact_details (
                scheme_id BIGINT NOT NULL,
                application_id BIGINT NOT NULL,
                beneficiary_id BIGINT,
                state INTEGER,
                district_id INTEGER,
                rural_urban INTEGER,
                block INTEGER,
                municipality INTEGER,
                gp INTEGER,
                ward INTEGER,
                villtowncity VARCHAR(150),
                postoffice VARCHAR(150),
                policestation VARCHAR(150),
                housepremiseno VARCHAR(150),
                pincode character(6),
                created_by_dist_code INTEGER,
                created_by_local_body_code INTEGER,
                created_by INTEGER,
                updated_by INTEGER,
                other_details JSONB,
                created_at TIMESTAMP(0),
                updated_at TIMESTAMP(0),
                is_clean SMALLINT DEFAULT 1,
                CONSTRAINT beneficiary_contact_details_pkey
                    PRIMARY KEY (application_id, scheme_id, is_clean),
                CONSTRAINT beneficiary_contact_details_application_unique
                    UNIQUE (application_id, scheme_id, is_clean)
            )
            PARTITION BY LIST (scheme_id)
        ");
        DB::statement("
    ALTER TABLE pension.beneficiary_contact_details
    ADD CONSTRAINT fk_bcd_application_id
    FOREIGN KEY (application_id)
    REFERENCES pension.unique_app_ben_ids(application_id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_contact_details
    ADD CONSTRAINT fk_bcd_beneficiary_id
    FOREIGN KEY (beneficiary_id)
    REFERENCES pension.unique_app_ben_ids(beneficiary_id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_contact_details
    ADD CONSTRAINT fk_bcd_scheme_id
    FOREIGN KEY (scheme_id)
    REFERENCES public.schemes(id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_contact_details
    ADD CONSTRAINT fk_bcd_district_id
    FOREIGN KEY (district_id)
    REFERENCES public.districts(id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_contact_details
    ADD CONSTRAINT fk_bcd_block
    FOREIGN KEY (block)
    REFERENCES public.blocks(id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_contact_details
    ADD CONSTRAINT fk_bcd_municipality
    FOREIGN KEY (municipality)
    REFERENCES public.municipalities(id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_contact_details
    ADD CONSTRAINT fk_bcd_gp
    FOREIGN KEY (gp)
    REFERENCES public.panchayats(id)
");
DB::statement("
    ALTER TABLE pension.beneficiary_contact_details
    ADD CONSTRAINT fk_bcd_ward
    FOREIGN KEY (ward)
    REFERENCES public.wards(id)
");

        $schemeIds = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 13, 17, 19, 20];
        $isCleans = [1, 2, 10];

        foreach ($schemeIds as $schemeId) {

            DB::statement("
                CREATE TABLE pension.bcd_s{$schemeId}
                PARTITION OF pension.beneficiary_contact_details
                FOR VALUES IN ({$schemeId})
                PARTITION BY LIST (is_clean)
            ");

            foreach ($isCleans as $isClean) {
                DB::statement("
                    CREATE TABLE pension.bcd_s{$schemeId}_c{$isClean}
                    PARTITION OF pension.bcd_s{$schemeId}
                    FOR VALUES IN ({$isClean})
                ");
            }

            DB::statement("
                CREATE TABLE pension.bcd_s{$schemeId}_default
                PARTITION OF pension.bcd_s{$schemeId}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE pension.bcd_default
            PARTITION OF pension.beneficiary_contact_details
            DEFAULT
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP TABLE IF EXISTS pension.beneficiary_contact_details CASCADE
        ");

        DB::statement("
            DROP SEQUENCE IF EXISTS pension.beneficiary_contact_details_id_seq
        ");
    }
};