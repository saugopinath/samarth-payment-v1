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
        Schema::table('ben_transaction_details_main_2022_2023_s1', function (Blueprint $table) {
            $table->foreign(['ben_id', 'scheme_id'], 'fk_ben_transaction_details_mains_ben_id')->references(['ben_id', 'scheme_id'])->on('ben_payment_details')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ben_transaction_details_main_2022_2023_s1', function (Blueprint $table) {
            $table->dropForeign('fk_ben_transaction_details_mains_ben_id');
        });
    }
};
