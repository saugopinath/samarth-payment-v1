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
        Schema::create('user_page_visit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_role_id')->nullable();
            $table->timestamp('visit_time')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('browser', 30)->nullable();
            $table->string('browser_version', 20)->nullable();
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('referrer')->nullable();
            $table->jsonb('description')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('visit_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_page_visit_logs');
    }
};
