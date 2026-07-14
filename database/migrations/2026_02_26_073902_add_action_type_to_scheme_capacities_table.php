
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
        if (!Schema::hasColumn('scheme_capacities', 'action_type')) {
            Schema::table('scheme_capacities', function (Blueprint $table) {
                $table->unsignedSmallInteger('action_type')->nullable()->after('capacity_type');
                $table->index('action_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('scheme_capacities', 'action_type')) {
            Schema::table('scheme_capacities', function (Blueprint $table) {
                $table->dropColumn('action_type');
            });
        }
    }
};
