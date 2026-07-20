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
        Schema::create('financial_year_month_payment_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('schemes')->onDelete('cascade');
            $table->string('financial_year')->index();
            $table->string('month')->index();
            $table->boolean('is_regular_lot')->default(false);
            $table->boolean('is_arrear_lot')->default(false);
            $table->string('type')->nullable();

            $table->timestamps();
            $table->unique(['scheme_id','financial_year', 'month','type']);
            $table->foreign('financial_year')->references('code')->on('financial_years')->onDelete('cascade');
            $table->foreign('month')->references('code')->on('months')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_year_month_lots');
    }
};
