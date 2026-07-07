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
        Schema::create('pension.unique_app_ben_ids', function (Blueprint $table) {
            $table->id('application_id');
            $table->unsignedBigInteger('beneficiary_id')->unique();
            $table->unsignedBigInteger('scheme_id')->references('id')->on('public.schemes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE pension.unique_app_ben_ids CASCADE;");
        //Schema::dropIfExists('pension.unique_app_ben_ids');
    }
};
