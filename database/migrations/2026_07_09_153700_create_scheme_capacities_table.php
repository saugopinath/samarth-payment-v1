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
            $table->bigIncrements('id');
            $table->bigInteger('scheme_id');
            $table->smallInteger('capacity_type')->index();
            $table->string('model_type');
            $table->bigInteger('model_id');
            $table->smallInteger('entry_type')->default(0);
            $table->string('total_capacity', 20);
            $table->text('extra_condition')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->string('action_type', 50)->nullable()->index();

            $table->index(['model_type', 'model_id']);
            $table->index(['scheme_id', 'entry_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_capacities');
    }
};
