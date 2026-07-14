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
        Schema::table('schemes', function (Blueprint $table) {
            $table->boolean('allow_entry')->default(true)->after('is_active');
            $table->boolean('allow_verification')->default(true)->after('allow_entry');
            $table->boolean('allow_approval')->default(true)->after('allow_verification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schemes', function (Blueprint $table) {
            $table->dropColumn(['allow_entry', 'allow_verification', 'allow_approval']);
        });
    }
};
