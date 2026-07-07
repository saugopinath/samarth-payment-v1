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
            $table->jsonb('request_payload')->nullable()->after('referrer');
            $table->jsonb('response_payload')->nullable()->after('request_payload');
            $table->integer('status_code')->nullable()->after('response_payload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_page_visit_logs', function (Blueprint $table) {
            $table->dropColumn(['request_payload', 'response_payload', 'status_code']);
            });
    }
};
