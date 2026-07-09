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
        Schema::create('districts', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('ref_code', 50)->nullable()->index();
            $table->string('lgd_code')->index();
            $table->string('name');
            $table->string('short_name');
            $table->string('local_name')->nullable();
            $table->smallInteger('state_id')->index();
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);

            $table->index(['id']);
            $table->unique(['lgd_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
