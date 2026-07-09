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
        Schema::table('subdivisions', function (Blueprint $table) {
            $table->foreign(['district_id'], 'district_id_fk')->references(['id'])->on('districts')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdivisions', function (Blueprint $table) {
            $table->dropForeign('district_id_fk');
        });
    }
};
