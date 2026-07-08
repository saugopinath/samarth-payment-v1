<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS payment');

        Schema::create('payment.ben_payment_details', function (Blueprint $table) {
            $table->integer('ben_id');
            $table->string('ben_name', 300);
            $table->smallInteger('scheme_id');
            $table->foreign('scheme_id')->references('id')->on('schemes');
            $table->string('last_accno', 50)->nullable();
            $table->string('last_ifsc', 11)->nullable();
            $table->foreign('last_ifsc')->references('code')->on('public.ifsccodemasters');
            $table->char('npci_bank_code', 4)->nullable();
            $table->char('aadhar_no', 12)->nullable();
            $table->integer('ben_status');
            $table->foreign('ben_status')->references('id')->on('codemasters');
            $table->integer('last_acc_validated')->default(0)->nullable();
            $table->foreign('last_acc_validated')->references('id')->on('codemasters');
            $table->jsonb('last_acc_validated_reason')->nullable();
            $table->integer('last_aadhar_validated')->default(0)->nullable();
            $table->foreign('last_aadhar_validated')->references('id')->on('codemasters');
            $table->jsonb('last_aadhar_validated_reason')->nullable();
            $table->integer('caste')->nullable();
            $table->foreign('caste')->references('id')->on('codemasters');
            $table->integer('gender')->nullable();
            $table->foreign('gender')->references('id')->on('codemasters');
            $table->string('mobile_no', 10)->nullable();
            $table->integer('created_by_dist_code');
            $table->foreign('created_by_dist_code')->references('id')->on('districts');

            $table->integer('created_by_sdo_code');
            $table->foreign('created_by_sdo_code')->references('id')->on('subdivisions');
            $table->integer('created_by_block_code');
            $table->foreign('created_by_block_code')->references('id')->on('blocks');
            $table->integer('dist_code')->nullable();
            $table->foreign('dist_code')->references('id')->on('districts');
            $table->smallInteger('rural_urban_id')->nullable();
            $table->foreign('rural_urban_id')->references('id')->on('codemasters');
            $table->integer('block_code')->nullable();
            $table->foreign('block_code')->references('id')->on('blocks');
            $table->integer('municipality_code')->nullable();
            $table->foreign('municipality_code')->references('id')->on('municipalities');
            $table->integer('gp_code')->nullable();
            $table->foreign('gp_code')->references('id')->on('panchayats');
            $table->integer('ward_code')->nullable();
            $table->foreign('ward_code')->references('id')->on('wards');

            // Using Laravel timestamp helpers
            $table->timestamps();   
            $table->softDeletes();
            
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('approval_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            $table->boolean('is_eligible')->default(true)->nullable();
            $table->jsonb('non_eligible_reason')->nullable();
            $table->smallInteger('is_rejected')->default(0)->nullable();
            $table->jsonb('rejection_cause')->nullable();
            
            $table->primary(['ben_id', 'scheme_id'], 'ben_payment_details_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment.ben_payment_details');
    }
};
