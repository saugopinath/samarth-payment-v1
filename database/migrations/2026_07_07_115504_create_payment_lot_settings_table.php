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
        Schema::create('payment_lot_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('schemes')->onDelete('cascade');
            $table->string('financial_year')->index();
            $table->string('month')->index();
            $table->boolean('is_regular_lot')->default(false);
            $table->boolean('is_arrear_lot')->default(false);
            $table->string('type', 10);

            $table->timestamps();
            $table->unique(['scheme_id','financial_year', 'month','type']);
            $table->foreign('financial_year')->references('code')->on('financial_years')->onDelete('cascade');
            $table->foreign('month')->references('code')->on('months')->onDelete('cascade');
            $table->foreign('type')->references('code')->on('codemasters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_lot_settings');
    }
};
