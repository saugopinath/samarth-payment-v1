<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ৩. রিকোয়েস্ট ট্রানজেকশন টেবিল
        Schema::create('dynamic_workflow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('dynamic_workflow_modules');
            $table->foreignId('scheme_id')->constrained('schemes');
            $table->unsignedBigInteger('ref_id')->index(); // e.g. application_id
            $table->unsignedInteger('current_rank')->index();
            $table->foreignId('current_step_id');
            $table->jsonb('old_data')->nullable(); // JSON formatted old values
            $table->jsonb('new_data')->nullable(); // JSON formatted new values
            $table->jsonb('changed_fields')->nullable();
            // $table->enum('status', ['pending', 'approved', 'reverted', 'rejected'])->default('pending');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->index(['module_id', 'ref_id'], 'dwr_module_ref_idx');
        });

        // ৪. অডিট লগ টেবিল
        // Schema::create('dynamic_workflow_logs', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('request_id')->constrained('dynamic_workflow_requests')->cascadeOnDelete();
        //     $table->unsignedInteger('from_rank');
        //     $table->unsignedInteger('to_rank');
        //     $table->enum('action', ['submitted', 'forwarded', 'approved', 'reverted', 'rejected']);
        //     $table->text('remark')->nullable();
        //     $table->unsignedBigInteger('user_id');
        //     $table->unsignedBigInteger('role_id');
        //     $table->timestamps();

        //     $table->index(['request_id', 'action']);
        // });
    }

    public function down(): void
    {
        // Schema::dropIfExists('dynamic_workflow_logs');
        Schema::dropIfExists('dynamic_workflow_requests');
    }
};
