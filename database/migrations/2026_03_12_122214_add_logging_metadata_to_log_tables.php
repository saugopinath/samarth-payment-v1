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
        Schema::table('user_page_visit_logs', function (Blueprint $table) {
            $table->string('log_level')->default('N')->after('status_code');
            $table->string('log_nickname')->nullable()->after('log_level');
        });

        Schema::table('livewire_action_logs', function (Blueprint $table) {
            $table->string('log_level')->default('N')->after('method_name');
            $table->string('log_nickname')->nullable()->after('log_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_page_visit_logs', function (Blueprint $table) {
            $table->dropColumn(['log_level', 'log_nickname']);
        });

        Schema::table('livewire_action_logs', function (Blueprint $table) {
            $table->dropColumn(['log_level', 'log_nickname']);
        });
    }
};
