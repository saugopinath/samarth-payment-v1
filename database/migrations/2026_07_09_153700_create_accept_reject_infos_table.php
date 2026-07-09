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
        Schema::create('accept_reject_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('scheme_id');
            $table->bigInteger('application_id')->nullable()->index();
            $table->bigInteger('beneficiary_id')->nullable()->index();
            $table->string('ip_address')->nullable();
            $table->smallInteger('user_id')->nullable();
            $table->string('browser')->nullable();
            $table->string('model_name')->nullable();
            $table->smallInteger('op_type')->nullable();
            $table->bigInteger('revert_reason_cause_id')->nullable();
            $table->string('revert_reason_remarks')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->char('old_op_type', 20)->nullable();
            $table->timestamps();
            $table->smallInteger('critical_changes')->default(0);
            $table->jsonb('old_value')->nullable();
            $table->jsonb('new_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accept_reject_infos');
    }
};
