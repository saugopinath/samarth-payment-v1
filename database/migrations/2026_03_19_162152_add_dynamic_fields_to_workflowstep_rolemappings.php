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
        Schema::table('workflowstep_rolemappings', function (Blueprint $table) {
            $table->unsignedBigInteger('module_id')->nullable()->after('scheme_id');
            $table->boolean('is_first_step')->default(false)->after('next_level_role_id');
            $table->boolean('is_final_step')->default(false)->after('is_first_step');
            $table->string('action_type', 50)->nullable()->after('is_final_step');
        });
    }

    public function down(): void
    {
        Schema::table('workflowstep_rolemappings', function (Blueprint $table) {
            $table->dropColumn(['module_id', 'is_first_step', 'is_final_step', 'action_type']);
        });
    }
};
