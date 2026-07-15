<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        protected $connection = 'pgsql_enc';

    public function up(): void
    {
        // Ensure the schema exists
        DB::connection($this->connection)->statement('CREATE SCHEMA IF NOT EXISTS jb_doc;');

        // Create the partitioned table
        DB::connection($this->connection)->statement('
            CREATE TABLE IF NOT EXISTS jb_doc.ben_attach_documents
            (
                id bigserial,
                beneficiary_id integer NOT NULL,
                scheme_id integer NOT NULL,
                document_type integer NOT NULL,
                attched_document text COLLATE pg_catalog."default",
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                deleted_at timestamp without time zone,
                created_by integer,
                ip_address character varying(50) COLLATE pg_catalog."default",
                document_extension character varying(20) COLLATE pg_catalog."default",
                document_mime_type character varying(100) COLLATE pg_catalog."default",
                created_by_dist_code integer NOT NULL,
                doc_type_name character varying(250) COLLATE pg_catalog."default",
                CONSTRAINT ben_attach_documents_pkey PRIMARY KEY (beneficiary_id, document_type, created_by_dist_code)
            ) PARTITION BY LIST (created_by_dist_code);
        ');

        // Fetch all district codes and create partitions
        $districtCodes = [303, 304, 664, 305, 307, 308, 309, 310, 311, 312, 313, 314, 703, 702, 315, 316, 317, 318, 319, 320, 704, 306, 321];
        
        foreach ($districtCodes as $code) {
            DB::connection($this->connection)->statement("
                CREATE TABLE IF NOT EXISTS jb_doc.ben_attach_documents_{$code} 
                PARTITION OF jb_doc.ben_attach_documents 
                FOR VALUES IN ({$code});
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection($this->connection)->statement('DROP TABLE IF EXISTS jb_doc.ben_attach_documents CASCADE;');
    }
};
