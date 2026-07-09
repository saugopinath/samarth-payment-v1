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
        Schema::create('scheme_payment_amounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('scheme_id');
            $table->string('financial_year');
            $table->decimal('january_amount', 10)->default(0);
            $table->decimal('february_amount', 10)->default(0);
            $table->decimal('march_amount', 10)->default(0);
            $table->decimal('april_amount', 10)->default(0);
            $table->decimal('may_amount', 10)->default(0);
            $table->decimal('june_amount', 10)->default(0);
            $table->decimal('july_amount', 10)->default(0);
            $table->decimal('august_amount', 10)->default(0);
            $table->decimal('september_amount', 10)->default(0);
            $table->decimal('october_amount', 10)->default(0);
            $table->decimal('november_amount', 10)->default(0);
            $table->decimal('december_amount', 10)->default(0);
            $table->timestamps();
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

            $table->unique(['scheme_id', 'financial_year'], 'unique_scheme_fy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_payment_amounts');
    }
};
