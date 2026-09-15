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
        Schema::create('ifms_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheme_id')->nullable();
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->string('party_code')->nullable();
            $table->string('ddo_code')->nullable();
            $table->string('hoa_id')->nullable();
            $table->string('hoa_user')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ifms_payment_settings');
    }
};
