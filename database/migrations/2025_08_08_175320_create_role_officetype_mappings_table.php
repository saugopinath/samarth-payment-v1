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
        Schema::create('role_office_type_mappings', function (Blueprint $table) {
            $table->id();
            $table->Integer('office_type_id');
            $table->Integer('role_id');
            $table->foreign('office_type_id','office_type_id_fk')->references('code')->on('codemasters')->onDelete('cascade'); 
            $table->foreign('role_id','role_id_fk')->references('id')->on('roles')->onDelete('cascade'); 
            $table->timestamps();
            $table->index('role_id');
            $table->index('office_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_office_type_mappings');
    }
};
