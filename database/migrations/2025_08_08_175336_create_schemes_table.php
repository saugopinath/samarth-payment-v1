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
            $table->tinyIncrements('id');
            $table->string('name');
            $table->string('short_name');
            $table->string('description')->nullable();
            $table->smallInteger('department_id');
            $table->foreign('department_id','department_id_fk')->references('id')->on('departments')->onDelete('cascade'); 
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->index(['name', 'short_name']);
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS schemes CASCADE');
    }
};
