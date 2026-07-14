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
            $table->id();
            $table->foreignId('scheme_id')->constrained('schemes')->onDelete('cascade');
            $table->string('financial_year'); // e.g. "2026-2027" or code
            
            // Monthly amounts
            $table->decimal('january_amount', 10, 2)->default(0);
            $table->decimal('february_amount', 10, 2)->default(0);
            $table->decimal('march_amount', 10, 2)->default(0);
            $table->decimal('april_amount', 10, 2)->default(0);
            $table->decimal('may_amount', 10, 2)->default(0);
            $table->decimal('june_amount', 10, 2)->default(0);
            $table->decimal('july_amount', 10, 2)->default(0);
            $table->decimal('august_amount', 10, 2)->default(0);
            $table->decimal('september_amount', 10, 2)->default(0);
            $table->decimal('october_amount', 10, 2)->default(0);
            $table->decimal('november_amount', 10, 2)->default(0);
            $table->decimal('december_amount', 10, 2)->default(0);
            
            $table->timestamps();
            
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
