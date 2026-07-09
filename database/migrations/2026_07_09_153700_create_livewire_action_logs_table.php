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
        Schema::create('livewire_action_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('url')->nullable();
            $table->string('ip')->nullable();
            $table->string('component_name')->nullable();
            $table->string('method_name')->nullable();
            $table->text('request_payload')->nullable();
            $table->text('response_payload')->nullable();
            $table->timestamps();
            $table->string('log_level')->default('N');
            $table->string('log_nickname')->nullable();
            $table->string('user_page_visit_log_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livewire_action_logs');
    }
};
