<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Scheme;
return new class extends Migration
{
    public function up(): void
    {
      
      //  DB::statement("CREATE SEQUENCE IF NOT EXISTS payment.lot_master_id_seq;");
        // Create parent partitioned table
        DB::statement("
            CREATE TABLE payment.lot_master (
                    id serial NOT NULL,
                    lot_month character varying(3),
                    lot_year character varying(9),
                    scheme_id integer,
                    ben_count bigint,
                    lot_status integer DEFAULT 1,
                    file_name character varying(50),
                    created_at timestamp with time zone DEFAULT now(),
                    updated_at timestamp with time zone,
                    payment_mode character varying(10),
                    lot_type_id integer,
                    CONSTRAINT lot_master_pkey PRIMARY KEY (id, scheme_id),
                    CONSTRAINT lot_master_ben_count_check CHECK (ben_count > 0)
            )
            PARTITION BY LIST (scheme_id)");
    DB::statement("
    ALTER TABLE payment.lot_master
    ADD CONSTRAINT fk_lot_master_lot_month
    FOREIGN KEY (lot_month)
    REFERENCES public.months(code)
");
DB::statement("
    ALTER TABLE payment.lot_master
    ADD CONSTRAINT fk_lot_master_lot_year
    FOREIGN KEY (lot_year)
    REFERENCES public.financial_years(code)
");
DB::statement("
    ALTER TABLE payment.lot_master
    ADD CONSTRAINT fk_lot_master_scheme_id
    FOREIGN KEY (scheme_id)
    REFERENCES public.schemes(id)
");
DB::statement("
    ALTER TABLE payment.lot_master
    ADD CONSTRAINT fk_lot_master_payment_mode
    FOREIGN KEY (payment_mode)
    REFERENCES public.codemasters(id)
");
DB::statement("
    ALTER TABLE payment.lot_master
    ADD CONSTRAINT fk_lot_master_lot_type_id
    FOREIGN KEY (lot_type_id)
    REFERENCES public.codemasters(id)
");

}

    public function down(): void
    {
        DB::statement("
            DROP TABLE IF EXISTS payment.lot_master CASCADE
        ");

       
    }
};