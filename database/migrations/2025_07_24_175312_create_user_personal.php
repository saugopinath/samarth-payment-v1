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
        Schema::create('user_personals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->foreign('user_id', 'user_id_fk')->references('id')->on('users');
            $table->string('name');
            $table->string('full_name_as_in_aadhaar')->nullable();
            $table->string('picture')->nullable();
            $table->date('date_hired')->nullable();
            $table->smallInteger('department_id')->nullable();
            $table->foreign('department_id', 'department_id_fk')->references('id')->on('departments');
            $table->timestamps();
            $table->smallInteger('is_active')->default(1);
            $table->index('id');
            $table->index('user_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_personals');
    }
};
