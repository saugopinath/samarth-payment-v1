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
            $table->mediumIncrements('id');
            $table->string('ref_code', 50)->unique();
            $table->string('lgd_code')->nullable();
            $table->string('name');
            $table->string('local_name')->nullable();
            $table->smallInteger('district_id');
            $table->foreign('district_id','district_id_fk')->references('id')->on('districts')->onDelete('cascade'); 
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->index('lgd_code');
            $table->index('id');
            $table->index('district_id');
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
