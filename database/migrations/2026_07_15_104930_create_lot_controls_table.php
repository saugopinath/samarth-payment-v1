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
        Schema::create('lot_controls', function (Blueprint $table) {
            $table->id();
            $table->morphs('blockable');
            $table->boolean('allow_regular_lot')->default(true);
            $table->boolean('allow_arrear_lot')->default(true);
            $table->string('supporting_document')->nullable();
            
            $table->unsignedBigInteger('last_block_by')->nullable();
            $table->unsignedBigInteger('last_unblock_by')->nullable();
            $table->timestamp('last_block_at')->nullable();
            $table->timestamp('last_unblock_at')->nullable();
            $table->string('last_block_ip')->nullable();
            $table->string('last_unblock_ip')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_controls');
    }
};
