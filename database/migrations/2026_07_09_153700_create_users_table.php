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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->char('mobile_no', 10)->index();
            $table->smallInteger('flag_sent_otp')->default(1);
            $table->boolean('first_time_set_password')->nullable();
            $table->timestamp('password_set_time')->nullable();
            $table->timestamp('password_expires_at')->nullable();
            $table->string('last_otp')->nullable();
            $table->timestamp('last_otp_generation_time')->nullable();
            $table->timestamp('last_otp_expire_time')->nullable();
            $table->smallInteger('is_active')->default(1);
            $table->smallInteger('is_login')->default(0);
            $table->boolean('bypass_otp')->default(false);
            $table->string('current_session_id')->nullable();
            $table->boolean('allow_multi_session')->default(false);
            $table->string('designation')->nullable();

            $table->unique(['email', 'is_active'], 'users_email_unique_index');
            $table->index(['id']);
            $table->unique(['mobile_no', 'is_active'], 'users_mobile_no_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
