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
            $table->mediumIncrements('id');
            $table->string('lgd_code')->unique();
            $table->string('ref_code')->index();
            $table->string('name');
            $table->Integer('municipality_id');
            $table->foreign('municipality_id','municipality_id_fk')->references('id')->on('municipalities')->onDelete('cascade'); 
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->smallInteger('ward_number')->nullable();
            $table->index('lgd_code');
            $table->index('id');
            $table->index('municipality_id');
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
