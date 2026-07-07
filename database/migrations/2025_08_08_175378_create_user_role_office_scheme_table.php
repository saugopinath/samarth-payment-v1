<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_role_scheme_office_mappings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();          
            $table->bigInteger('user_id');
            $table->bigInteger('role_id');
            $table->bigInteger('office_id');
            $table->bigInteger('scheme_id');
            $table->foreign('user_id', 'user_id_fk')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id', 'role_id_fk')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('office_id', 'office_id_fk')->references('id')->on('office_masters')->onDelete('cascade');
            $table->foreign('scheme_id', 'scheme_id_fk')->references('id')->on('schemes')->onDelete('cascade');
            $table->smallInteger('is_active')->default(1);
            $table->index('user_id');
            $table->index('id');
            $table->index('office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_role_scheme_office_mappings');
    }
};
