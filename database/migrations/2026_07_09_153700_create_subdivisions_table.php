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
        Schema::create('subdivisions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref_code', 50)->unique();
            $table->string('lgd_code')->nullable()->index();
            $table->string('name');
            $table->string('local_name')->nullable();
            $table->smallInteger('district_id')->index();
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);

            $table->index(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdivisions');
    }
};
