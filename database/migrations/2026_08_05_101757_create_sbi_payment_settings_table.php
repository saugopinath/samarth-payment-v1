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
        Schema::create('sbi_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheme_id')->nullable();
            $table->text('npci_user_id')->nullable();
            $table->text('npci_user_name')->nullable();
            $table->text('bank_account_no')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbi_payment_settings');
    }
};
