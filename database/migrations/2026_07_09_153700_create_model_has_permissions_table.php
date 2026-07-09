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
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->bigInteger('permission_id');
            $table->string('model_type');
            $table->bigInteger('model_id');
            $table->bigInteger('scheme_id')->nullable();

            $table->index(['model_id', 'model_type']);
            $table->unique(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_permissions');
    }
};
