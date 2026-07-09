<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\FinancialYear;
use App\Models\Scheme;
return new class extends Migration
{
    public function up(): void
    {
      

        // Create parent partitioned table
        DB::statement("
            CREATE TABLE payment.ben_transaction_details (
                ben_id integer NOT NULL,
                scheme_id smallint NOT NULL,
                apr_lot_no numeric(6,0),
                apr_lot_type character(1),
                apr_lot_status character(1),
                apr_is_eligible boolean DEFAULT true,
                apr_eligible_amount integer DEFAULT 0,
                apr_payment_amount integer DEFAULT 0,
                may_lot_no numeric(6,0),
                may_lot_type character(1) ,
                may_lot_status character(1) ,
                may_is_eligible boolean DEFAULT true,
                may_eligible_amount integer DEFAULT 0,
                may_payment_amount integer DEFAULT 0,
                jun_lot_no numeric(6,0),
                jun_lot_type character(1) ,
                jun_lot_status character(1) ,
                jun_is_eligible boolean DEFAULT true,
                jun_eligible_amount integer DEFAULT 0,
                jun_payment_amount integer DEFAULT 0,
                jul_lot_no numeric(6,0),
                jul_lot_type character(1) ,
                jul_lot_status character(1) ,
                jul_is_eligible boolean DEFAULT true,
                jul_eligible_amount integer DEFAULT 0,
                jul_payment_amount integer DEFAULT 0,
                aug_lot_no numeric(6,0),
                aug_lot_type character(1) ,
                aug_lot_status character(1) ,
                aug_is_eligible boolean DEFAULT true,
                aug_eligible_amount integer DEFAULT 0,
                aug_payment_amount integer DEFAULT 0,
                sep_lot_no numeric(6,0),
                sep_lot_type character(1) ,
                sep_lot_status character(1) ,
                sep_is_eligible boolean DEFAULT true,
                sep_eligible_amount integer DEFAULT 0,
                sep_payment_amount integer DEFAULT 0,
                oct_lot_no numeric(6,0),
                oct_lot_type character(1) ,
                oct_lot_status character(1) ,
                oct_is_eligible boolean DEFAULT true,
                oct_eligible_amount integer DEFAULT 0,
                oct_payment_amount integer DEFAULT 0,
                nov_lot_no numeric(6,0),
                nov_lot_type character(1) ,
                nov_lot_status character(1) ,
                nov_is_eligible boolean DEFAULT true,
                nov_eligible_amount integer DEFAULT 0,
                nov_payment_amount integer DEFAULT 0,
                dec_lot_no numeric(6,0),
                dec_lot_type character(1) ,
                dec_lot_status character(1) ,
                dec_is_eligible boolean DEFAULT true,
                dec_eligible_amount integer DEFAULT 0,
                dec_payment_amount integer DEFAULT 0,
                jan_lot_no numeric(6,0),
                jan_lot_type character(1) ,
                jan_lot_status character(1) ,
                jan_is_eligible boolean DEFAULT true,
                jan_eligible_amount integer DEFAULT 0,
                jan_payment_amount integer DEFAULT 0,
                feb_lot_no numeric(6,0),
                feb_lot_type character(1) ,
                feb_lot_status character(1) ,
                feb_is_eligible boolean DEFAULT true,
                feb_eligible_amount integer DEFAULT 0,
                feb_payment_amount integer DEFAULT 0,
                mar_lot_no numeric(6,0),
                mar_lot_type character(1) ,
                mar_lot_status character(1) ,
                mar_is_eligible boolean DEFAULT true,
                mar_eligible_amount integer DEFAULT 0,
                mar_payment_amount integer DEFAULT 0,
                fin_year character(9)
            )
            PARTITION BY LIST (fin_year)");
    DB::statement("
    ALTER TABLE payment.ben_transaction_details
    ADD CONSTRAINT fk_btd_ben_id
    FOREIGN KEY (ben_id)
    REFERENCES payment.ben_payment_details(ben_id)
");

DB::statement("
    ALTER TABLE payment.ben_transaction_details
    ADD CONSTRAINT fk_btd_scheme_id
    FOREIGN KEY (scheme_id)
    REFERENCES public.schemes(id)
");
DB::statement("
    ALTER TABLE payment.ben_transaction_details
    ADD CONSTRAINT fk_btd_fin_year
    FOREIGN KEY (fin_year)
    REFERENCES public.financial_years(code)
");
DB::statement("
    ALTER TABLE payment.ben_transaction_details
    ADD CONSTRAINT fk_btd_lot_no
    FOREIGN KEY (fin_year)
    REFERENCES payment.lot_masters(lot_no)
");




         $finyears = FinancialYear::pluck('code')->where('is_active', 1)->toArray();
          $schemeIds = Scheme::pluck('id')->where('is_active', 1)->toArray();
       // $isCleans = [1, 2, 10];

        foreach ($finyears as $fin_year_item) {

            DB::statement("
                CREATE TABLE payment.btd_s{$fin_year_item}
                PARTITION OF payment.ben_transaction_details
                FOR VALUES IN ({$fin_year_item})
                PARTITION BY LIST (is_clean)
            ");

            foreach ($schemeIds as $schemeItem) {
                DB::statement("
                    CREATE TABLE payment.btd_s{$fin_year_item}_c{$schemeItem}
                    PARTITION OF payment.btd_s{$fin_year_item}
                    FOR VALUES IN ({$schemeItem})
                ");
            }

            DB::statement("
                CREATE TABLE payment.btd_s{$fin_year_item}_default
                PARTITION OF payment.btd_s{$fin_year_item}
                DEFAULT
            ");
        }

        DB::statement("
            CREATE TABLE payment.btd_default
            PARTITION OF payment.ben_transaction_details
            DEFAULT
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP TABLE IF EXISTS payment.ben_transaction_details CASCADE
        ");

       
    }
};