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
        Schema::create('payment_main_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('schemes')->onDelete('cascade');
            $table->string('financial_year')->constrained('financial_years')->onDelete('cascade');
            // Monthly 
            $table->jsonb('jan');
            $table->jsonb('feb');
            $table->jsonb('mar');
            $table->jsonb('apr');
            $table->jsonb('may');
            $table->jsonb('jun');
            $table->jsonb('jul');
            $table->jsonb('aug');
            $table->jsonb('sep');
            $table->jsonb('oct');
            $table->jsonb('nov');
            $table->jsonb('dec');
          

        

            $table->timestamps();
            $table->unique(['scheme_id', 'financial_year'], 'unique_scheme_fy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_main_settings');
    }
};
