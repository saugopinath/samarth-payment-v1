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
        Schema::table('user_personals', function (Blueprint $table) {
            $table->foreign(['department_id'], 'department_id_fk')->references(['id'])->on('departments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'], 'user_id_fk')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_personals', function (Blueprint $table) {
            $table->dropForeign('department_id_fk');
            $table->dropForeign('user_id_fk');
        });
    }
};
