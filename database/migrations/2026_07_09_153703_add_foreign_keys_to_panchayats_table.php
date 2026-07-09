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
        Schema::table('panchayats', function (Blueprint $table) {
            $table->foreign(['block_id'], 'block_id_fk')->references(['id'])->on('blocks')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panchayats', function (Blueprint $table) {
            $table->dropForeign('block_id_fk');
        });
    }
};
