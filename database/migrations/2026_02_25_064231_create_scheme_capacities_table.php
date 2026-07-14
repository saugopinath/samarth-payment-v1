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
        Schema::create('scheme_capacities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheme_id');
            $table->smallInteger('capacity_type'); // ['1 - full_scheme', '2- location']
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->smallInteger('entry_type')->default(0); // ['1 - normal', '2- ds Entry']
            $table->string('total_capacity', 20);
            $table->text('extra_condition')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['scheme_id', 'entry_type']);
            $table->index(['model_type', 'model_id']);
            $table->index(['capacity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheme_capacities');
    }
};