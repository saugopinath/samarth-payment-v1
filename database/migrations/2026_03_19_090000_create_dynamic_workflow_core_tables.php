<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ১. মডিউল মাস্টার টেবিল
        Schema::create('dynamic_workflow_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('scheme_id')->index();
            $table->string('module_code', 60)->unique(); // e.g. BANK_UPD
            $table->string('module_name', 150);
            $table->unsignedInteger('step_count')->default(1);
            $table->jsonb('allowed_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        Schema::create('dynamic_workflow_labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('scheme_id')->index();
            $table->foreignId('module_id')->constrained('dynamic_workflow_modules');
            $table->string('label_name', 150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_workflow_labels');
        Schema::dropIfExists('dynamic_workflow_modules');
    }
};
