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
        Schema::table('audits', function (Blueprint $table) {
            $table->string('user_page_visit_log_id')->nullable()->index();
        });

        Schema::table('livewire_action_logs', function (Blueprint $table) {
            $table->string('user_page_visit_log_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn('user_page_visit_log_id');
        });

        Schema::table('livewire_action_logs', function (Blueprint $table) {
            $table->dropColumn('user_page_visit_log_id');
        });
    }
};
