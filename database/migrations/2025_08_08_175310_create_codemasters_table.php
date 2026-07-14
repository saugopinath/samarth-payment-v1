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
        Schema::create('codemasters', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name');
            $table->string('short_name');
            $table->smallInteger('parent_id')->nullable();
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->smallInteger('code')->nullable()->unique();
            $table->smallInteger('rank')->nullable();
            $table->string('parent_short_code')->nullable();
            $table->index('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS codemasters CASCADE');
    }
};
