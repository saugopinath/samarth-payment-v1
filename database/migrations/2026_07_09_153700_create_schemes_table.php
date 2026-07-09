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
        Schema::create('schemes', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name');
            $table->string('short_name');
            $table->string('description')->nullable();
            $table->smallInteger('department_id')->index();
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->smallInteger('min_age')->nullable();
            $table->smallInteger('max_age')->nullable();
            $table->decimal('base_amount', 10)->nullable();
            $table->string('display_name')->nullable();
            $table->boolean('allow_entry')->default(true);
            $table->boolean('allow_verification')->default(true);
            $table->boolean('allow_approval')->default(true);
            $table->boolean('allow_regular_lot')->default(true);
            $table->boolean('allow_arrear_lot')->default(true);

            $table->index(['name', 'short_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schemes');
    }
};
