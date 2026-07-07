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
        Schema::table('dynamic_workflow_labels', function (Blueprint $table) {
            $table->unsignedBigInteger('op_type_id')->nullable()->after('module_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_workflow_labels', function (Blueprint $table) {
            $table->dropColumn('op_type_id');
        });
    }
};
