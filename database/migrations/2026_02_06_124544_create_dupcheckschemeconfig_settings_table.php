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
        Schema::create('dupcheckschemeconfig_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('scheme_id');
            $table->boolean('is_same')->default(false);
            $table->boolean('is_cross')->default(false);
            $table->jsonb('scheme_lists')->nullable();
            $table->string('check_with', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dupcheckschemeconfig_settings');
    }
};
