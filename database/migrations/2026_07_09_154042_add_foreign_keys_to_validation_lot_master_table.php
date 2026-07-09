<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('validation_lot_master', function (Blueprint $table) {
            $table->foreign(['cur_status'], 'fk_payment_lot_master_cur_status')->references(['id'])->on('codemasters')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['lot_month'], 'fk_payment_lot_master_month')->references(['code'])->on('months')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['scheme_id'], 'fk_payment_lot_master_scheme')->references(['id'])->on('schemes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['validation_type_id'], 'fk_payment_lot_master_validation_type_id')->references(['id'])->on('codemasters')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['lot_year'], 'fk_payment_lot_master_year')->references(['code'])->on('financial_years')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('validation_lot_master', function (Blueprint $table) {
            $table->dropForeign('fk_payment_lot_master_cur_status');
            $table->dropForeign('fk_payment_lot_master_month');
            $table->dropForeign('fk_payment_lot_master_scheme');
            $table->dropForeign('fk_payment_lot_master_validation_type_id');
            $table->dropForeign('fk_payment_lot_master_year');
        });
    }
};
