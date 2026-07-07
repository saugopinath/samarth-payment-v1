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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('rank');
            $table->foreignId('scheme_id')
                ->constrained('schemes')
                ->cascadeOnDelete();
            $table->string('label');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->nullOnDelete();
            $table->boolean('is_first')->default(false);
            $table->boolean('is_last')->default(false);
            $table->timestamps();
            $table->unique(['scheme_id', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
