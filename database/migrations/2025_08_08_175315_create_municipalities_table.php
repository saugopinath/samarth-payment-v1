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
        Schema::create('municipalities', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('lgd_code')->unique();
            $table->string('ref_code', 50)->index()->nullable();
            $table->string('name');
            $table->string('local_name')->nullable();
            $table->Integer('subdivision_id');
            $table->foreign('subdivision_id','subdivision_id_fk')->references('id')->on('subdivisions')->onDelete('cascade'); 
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->index('lgd_code');
            $table->index('subdivision_id');
            $table->index('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
