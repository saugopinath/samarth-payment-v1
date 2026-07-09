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
        Schema::create('financial_year_month_lots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('financial_year')->index();
            $table->string('month')->index();
            $table->boolean('is_regular_lot')->default(false);
            $table->boolean('is_arrear_lot')->default(false);
            $table->timestamps();
            $table->bigInteger('scheme_id')->nullable();

            $table->unique(['financial_year', 'month', 'scheme_id']);
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
