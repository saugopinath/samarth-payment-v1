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
        Schema::table('user_role_scheme_office_mappings', function (Blueprint $table) {
            $table->foreign(['office_id'], 'office_id_fk')->references(['id'])->on('office_masters')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'], 'role_id_fk')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['scheme_id'], 'scheme_id_fk')->references(['id'])->on('schemes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'user_id_fk')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_role_scheme_office_mappings', function (Blueprint $table) {
            $table->dropForeign('office_id_fk');
            $table->dropForeign('role_id_fk');
            $table->dropForeign('scheme_id_fk');
            $table->dropForeign('user_id_fk');
        });
    }
};
