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
            $table->id();
            $table->integer('scheme_id');
            $table->unsignedBigInteger('application_id')->nullable();
            $table->unsignedBigInteger('beneficiary_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->smallInteger('user_id')->nullable();
            $table->string('browser')->nullable();
            $table->string('model_name')->nullable();
            $table->smallInteger('op_type')->nullable();
            $table->unsignedBigInteger('revert_reason_cause_id')->nullable();
            $table->string('revert_reason_remarks')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->char('old_op_type', 20)->nullable();
            // $table->foreign('application_id', 'application_id_fk')->references('application_id')->on('pension.unique_app_ben_ids');
            $table->foreign('op_type', 'op_type_fk')->references('id')->on('codemasters')->onDelete('cascade');
            $table->foreign('user_id', 'user_id_fk')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('revert_reason_cause_id', 'reject_revert_reason_id_fk')
                ->references('id')
                ->on('public.codemasters');
            $table->timestamps();
            $table->index('application_id');
            $table->index('beneficiary_id');
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
