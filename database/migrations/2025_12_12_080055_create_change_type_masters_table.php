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
        Schema::create('change_type_masters', function (Blueprint $table) {
           $table->tinyIncrements('id');
            $table->string('name');
            $table->string('short_name');
            $table->smallInteger('code')->nullable()->unique();
            $table->smallInteger('is_active')->default(1);
            $table->timestamps();
            $table->index('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_type_masters');
    }
};
