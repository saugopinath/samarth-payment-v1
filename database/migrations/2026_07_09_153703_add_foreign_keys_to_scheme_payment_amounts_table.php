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
        Schema::table('scheme_payment_amounts', function (Blueprint $table) {
            $table->foreign(['scheme_id'])->references(['id'])->on('schemes')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheme_payment_amounts', function (Blueprint $table) {
            $table->dropForeign('scheme_payment_amounts_scheme_id_foreign');
        });
    }
};
