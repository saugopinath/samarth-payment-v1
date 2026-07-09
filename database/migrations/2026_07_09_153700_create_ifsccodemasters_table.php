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
        Schema::create('ifsccodemasters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 11)->index();
            $table->timestamps();
            $table->string('branch');
            $table->smallInteger('state_id')->index();
            $table->integer('bankmaster_id')->index();
            $table->smallInteger('is_active')->default(1);

            $table->unique(['code']);
            $table->index(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ifsccodemasters');
    }
};
