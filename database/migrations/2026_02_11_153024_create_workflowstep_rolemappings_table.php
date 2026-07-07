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
        Schema::create('workflowstep_rolemappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')
                ->constrained('schemes')
                ->cascadeOnDelete();
            $table->integer('workflow_step_id');
            $table->bigInteger('rank')->nullable();
            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();
            $table->bigInteger('same_level_role_id')->nullable();
            $table->bigInteger('next_level_role_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflowstep_rolemappings');
    }
};
