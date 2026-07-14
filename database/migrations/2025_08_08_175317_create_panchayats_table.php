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
        Schema::create('panchayats', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('lgd_code');
            $table->string('ref_code')->index();
            $table->string('name');
            $table->smallInteger('block_id');
            $table->foreign('block_id', 'block_id_fk')->references('id')->on('blocks')->onDelete('cascade'); 
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->index('lgd_code');
            $table->index('id');
            $table->index('block_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panchayats');
    }
};
