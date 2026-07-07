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
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->char('mobile_no', length: 10);
            $table->smallInteger('flag_sent_otp')->default(1);
            $table->boolean('first_time_set_password')->nullable();
            $table->timestamp('password_set_time')->nullable();
            $table->timestamp('password_expires_at')->nullable();
            $table->string('last_otp')->nullable();
            $table->timestamp('last_otp_generation_time')->nullable();
            $table->timestamp('last_otp_expire_time')->nullable();
            $table->smallInteger('is_active')->default(1);
            $table->index('mobile_no');
            $table->index('email');
            $table->index('id');
        });
        

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE public.users CASCADE;");
        //Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
