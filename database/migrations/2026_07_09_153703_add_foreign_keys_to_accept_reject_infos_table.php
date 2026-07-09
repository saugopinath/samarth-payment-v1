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
        Schema::table('accept_reject_infos', function (Blueprint $table) {
            $table->foreign(['op_type'], 'op_type_fk')->references(['id'])->on('codemasters')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['revert_reason_cause_id'], 'reject_revert_reason_id_fk')->references(['id'])->on('codemasters')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'], 'user_id_fk')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accept_reject_infos', function (Blueprint $table) {
            $table->dropForeign('op_type_fk');
            $table->dropForeign('reject_revert_reason_id_fk');
            $table->dropForeign('user_id_fk');
        });
    }
};
