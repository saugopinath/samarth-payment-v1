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
        Schema::table('financial_year_month_lots', function (Blueprint $table) {
            // Drop old unique constraint
            $table->dropUnique(['financial_year', 'month']);
            
            // Add scheme_id column
            $table->unsignedBigInteger('scheme_id')->nullable()->after('id');
            
            // Add new unique constraint
            $table->unique(['financial_year', 'month', 'scheme_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_year_month_lots', function (Blueprint $table) {
            $table->dropUnique(['financial_year', 'month', 'scheme_id']);
            $table->dropColumn('scheme_id');
            $table->unique(['financial_year', 'month']);
        });
    }
};
