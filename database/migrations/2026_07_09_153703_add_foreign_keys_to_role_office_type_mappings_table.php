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
        Schema::table('role_office_type_mappings', function (Blueprint $table) {
            $table->foreign(['role_id'], 'role_id_fk')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_office_type_mappings', function (Blueprint $table) {
            $table->dropForeign('role_id_fk');
        });
    }
};
