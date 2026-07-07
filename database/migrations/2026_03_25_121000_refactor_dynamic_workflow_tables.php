<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the new dynamic_workflow_scheme_modules table
        Schema::create('dynamic_workflow_scheme_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('schemes');
            $table->foreignId('module_id')->constrained('dynamic_workflow_modules')->cascadeOnDelete();
            $table->string('main_module_code', 150)->nullable();
            $table->unsignedInteger('step_count')->default(1);
            $table->timestamps();
        });

        // 2. Transfer existing data if any (optional, but let's drop columns after)
        // Since we are moving to a new structure, let's copy existing modules to scheme_modules
        // $existingModules = DB::table('dynamic_workflow_modules')->get();
        // foreach ($existingModules as $module) {
        //     DB::table('dynamic_workflow_scheme_modules')->insert([
        //         'scheme_id' => $module->scheme_id,
        //         'module_id' => $module->id,
        //         'main_module_code' => $module->module_code,
        //         'step_count' => $module->step_count,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        // 3. Drop scheme_id and step_count from dynamic_workflow_modules
        Schema::table('dynamic_workflow_modules', function (Blueprint $table) {
            $table->dropIndex(['scheme_id']);
            $table->dropColumn(['scheme_id', 'step_count']);
        });

        // 4. Update foreign keys in dynamic_workflow_labels and dynamic_workflow_requests
        Schema::table('dynamic_workflow_labels', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->foreign('module_id')->references('id')->on('dynamic_workflow_scheme_modules')->cascadeOnDelete();
        });

        Schema::table('dynamic_workflow_requests', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->foreign('module_id')->references('id')->on('dynamic_workflow_scheme_modules')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dynamic_workflow_requests', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->foreign('module_id')->references('id')->on('dynamic_workflow_modules')->cascadeOnDelete();
        });

        Schema::table('dynamic_workflow_labels', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->foreign('module_id')->references('id')->on('dynamic_workflow_modules')->cascadeOnDelete();
        });

        Schema::table('dynamic_workflow_modules', function (Blueprint $table) {
            $table->unsignedInteger('scheme_id')->index()->nullable();
            $table->unsignedInteger('step_count')->default(1);
        });

        Schema::dropIfExists('dynamic_workflow_scheme_modules');
    }
};
