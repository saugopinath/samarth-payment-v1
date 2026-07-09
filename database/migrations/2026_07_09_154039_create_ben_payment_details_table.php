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
        Schema::create('ben_payment_details', function (Blueprint $table) {
            $table->integer('ben_id');
            $table->string('ben_name', 300);
            $table->smallInteger('scheme_id');
            $table->string('last_accno', 50)->nullable();
            $table->string('last_ifsc', 11)->nullable();
            $table->char('npci_bank_code', 4)->nullable();
            $table->char('aadhar_no', 12)->nullable();
            $table->integer('ben_status');
            $table->integer('last_acc_validated')->nullable()->default(0);
            $table->jsonb('last_acc_validated_reason')->nullable();
            $table->integer('last_aadhar_validated')->nullable()->default(0);
            $table->jsonb('last_aadhar_validated_reason')->nullable();
            $table->integer('caste')->nullable();
            $table->integer('gender')->nullable();
            $table->string('mobile_no', 10)->nullable();
            $table->integer('created_by_dist_code');
            $table->integer('created_by_sdo_code');
            $table->integer('created_by_block_code');
            $table->integer('dist_code')->nullable();
            $table->smallInteger('rural_urban_id')->nullable();
            $table->integer('block_code')->nullable();
            $table->integer('municipality_code')->nullable();
            $table->integer('gp_code')->nullable();
            $table->integer('ward_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('approval_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->boolean('is_eligible')->nullable()->default(true);
            $table->jsonb('non_eligible_reason')->nullable();
            $table->smallInteger('is_rejected')->nullable()->default(0);
            $table->jsonb('rejection_cause')->nullable();

            $table->primary(['ben_id', 'scheme_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ben_payment_details');
    }
};
