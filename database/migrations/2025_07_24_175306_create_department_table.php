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
        Schema::create('departments', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name');
            $table->string('short_name');
            $table->string('logo')->nullable();
            $table->foreignId('state_id')->constrained()->index();
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->index(['name', 'short_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
