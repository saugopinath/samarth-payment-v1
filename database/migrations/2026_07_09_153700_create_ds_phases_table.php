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
        Schema::create('ds_phases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('phase_code')->unique();
            $table->text('phase_desc');
            $table->boolean('is_current')->default(false);
            $table->date('base_dob');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ds_phases');
    }
};
