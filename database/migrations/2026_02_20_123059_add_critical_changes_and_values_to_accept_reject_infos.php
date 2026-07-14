<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accept_reject_infos', function (Blueprint $table) {
            $table->smallInteger('critical_changes')->default(0)->after('parent_id');
            $table->jsonb('old_value')->nullable()->after('critical_changes');
            $table->jsonb('new_value')->nullable()->after('old_value');
        });
    }

    public function down(): void
    {
        Schema::table('accept_reject_infos', function (Blueprint $table) {
            $table->dropColumn(['critical_changes', 'old_value', 'new_value']);
        });
    }
};
