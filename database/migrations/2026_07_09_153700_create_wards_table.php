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
        Schema::create('wards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lgd_code')->index();
            $table->string('ref_code')->index();
            $table->string('name');
            $table->integer('municipality_id')->index();
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->smallInteger('ward_number')->nullable();

            $table->index(['id']);
            $table->unique(['lgd_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};
