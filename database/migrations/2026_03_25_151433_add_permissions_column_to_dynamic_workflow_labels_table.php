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
            $table->json('permissions')->nullable()->after('op_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_workflow_labels', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
