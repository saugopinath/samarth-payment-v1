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
        Schema::create('states', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('ref_code', 50)->index()->nullable();
            $table->string('lgd_code')->unique();
            $table->string('name');
            $table->string('local_name')->nullable();
            $table->enum('state_ut', ['State', 'UT']);
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->index(['name', 'state_ut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
