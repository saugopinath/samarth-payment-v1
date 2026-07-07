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
        Schema::create('user_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('old_password')->nullable();
            $table->string('new_password')->nullable();
            $table->smallInteger('operation_type')->nullable();
            $table->smallInteger('operate_by')->nullable();
            $table->smallInteger('operate_to_user_id')->nullable();
            $table->string('ip_address')->nullable(); 
            $table->string('user_agent')->nullable(); 
            $table->timestamp('operation_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_audit_trails');
    }
};
