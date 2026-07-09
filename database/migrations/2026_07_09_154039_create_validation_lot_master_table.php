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
        Schema::create('validation_lot_master', function (Blueprint $table) {
            $table->increments('lot_no');
            $table->string('lot_month', 3)->nullable();
            $table->string('lot_year', 9)->nullable();
            $table->smallInteger('scheme_id');
            $table->integer('validation_type_id')->nullable();
            $table->integer('cur_status')->nullable();
            $table->string('file_name', 50)->nullable();
            $table->timestamps();
            $table->timestamp('validation_push_date')->nullable();
            $table->timestamp('response_receive_date')->nullable();
            $table->integer('ben_count')->nullable()->default(0);
            $table->integer('success_count')->nullable()->default(0);
            $table->integer('failed_count')->nullable()->default(0);
            $table->timestampTz('last_response_check_date')->nullable();

            $table->primary(['lot_no', 'scheme_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_lot_master');
    }
};
