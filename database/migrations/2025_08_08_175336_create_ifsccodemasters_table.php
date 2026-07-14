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
            $table->id();
            $table->string('code',11)->unique();
            $table->timestamps();
            $table->string('branch');
            $table->smallInteger('state_id');
            $table->Integer('bankmaster_id');
            $table->foreign('state_id','state_id_fk')->references('id')->on('states')->onDelete('cascade'); 
            $table->foreign('bankmaster_id','bankmaster_id_fk')->references('id')->on('bankmasters')->onDelete('cascade'); 
            $table->smallInteger('is_active')->default(1);
            $table->index('code');
            $table->index('bankmaster_id');
            $table->index('state_id');
            $table->index('id');
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
