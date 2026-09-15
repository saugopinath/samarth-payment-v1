<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    protected $connection = 'pgsql_payment';

    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE payment.payment_lot_master ADD COLUMN IF NOT EXISTS int_type character varying(10)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE payment.payment_lot_master DROP COLUMN IF EXISTS int_type");
    }
};
