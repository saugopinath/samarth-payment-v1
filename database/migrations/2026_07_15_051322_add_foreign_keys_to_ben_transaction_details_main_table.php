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

    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $table = 'payment.ben_monthwise_payment_status';
    
        foreach ($months as $month_item) {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT fk_ben_monthwise_payment_status_{$month_item}_lot_no FOREIGN KEY ({$month_item}_lot_no,financial_year,scheme_id) REFERENCES payment.payment_lot_master (lot_no,lot_year,scheme_id)");
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT fk_ben_monthwise_payment_status_{$month_item}_lot_type FOREIGN KEY ({$month_item}_lot_type) REFERENCES public.codemasters (code)");
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT fk_ben_monthwise_payment_status_{$month_item}_lot_status FOREIGN KEY ({$month_item}_lot_status) REFERENCES public.codemasters (code)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $table = 'payment.ben_monthwise_payment_status';
        
        foreach ($months as $month_item) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS fk_ben_monthwise_payment_status_{$month_item}_lot_no");
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS fk_ben_monthwise_payment_status_{$month_item}_lot_type");
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS fk_ben_monthwise_payment_status_{$month_item}_lot_status");
        }
    }
};
