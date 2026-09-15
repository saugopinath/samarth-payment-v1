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
        Schema::create('api_response_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_name')->nullable();
            $table->text('request_data')->nullable();
            $table->integer('http_status')->nullable();
            $table->longText('response_data')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_response_logs');
    }
};
