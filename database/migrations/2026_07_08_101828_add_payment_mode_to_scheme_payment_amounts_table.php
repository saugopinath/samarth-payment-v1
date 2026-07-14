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
            $table->string('january_payment_mode')->nullable();
            $table->string('february_payment_mode')->nullable();
            $table->string('march_payment_mode')->nullable();
            $table->string('april_payment_mode')->nullable();
            $table->string('may_payment_mode')->nullable();
            $table->string('june_payment_mode')->nullable();
            $table->string('july_payment_mode')->nullable();
            $table->string('august_payment_mode')->nullable();
            $table->string('september_payment_mode')->nullable();
            $table->string('october_payment_mode')->nullable();
            $table->string('november_payment_mode')->nullable();
            $table->string('december_payment_mode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheme_payment_amounts', function (Blueprint $table) {
            $table->dropColumn([
                'january_payment_mode',
                'february_payment_mode',
                'march_payment_mode',
                'april_payment_mode',
                'may_payment_mode',
                'june_payment_mode',
                'july_payment_mode',
                'august_payment_mode',
                'september_payment_mode',
                'october_payment_mode',
                'november_payment_mode',
                'december_payment_mode',
            ]);
        });
    }
};
