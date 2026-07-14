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
        Schema::create('districts', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('ref_code', 50)->index()->nullable();
            $table->string('lgd_code')->unique();
            $table->string('name');
            $table->string('short_name');
            $table->string('local_name')->nullable();
            $table->smallInteger('state_id');
            $table->foreign('state_id', 'state_id_fk')->references('id')->on('states')->onDelete('cascade'); 
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->index('lgd_code');
            $table->index('id');
            $table->index('state_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
