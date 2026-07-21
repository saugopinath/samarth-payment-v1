<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = [
            'pgsql_bandhan' => 'bandhan',
            'pgsql_sbi' => 'sbi',
            'pgsql_ifms' => 'ifms',
            'pgsql_ifms_v3' => 'ifms_v3'
        ];

        foreach ($connections as $connection => $schema) {
            DB::connection($connection)->statement("CREATE SCHEMA IF NOT EXISTS {$schema}");
            // DB::connection($connection)->statement('DROP TABLE IF EXISTS codemasters CASCADE');

            Schema::connection($connection)->create('codemasters', function (Blueprint $table) {
                $table->tinyIncrements('id');
                $table->string('name');
                $table->string('short_name');
                $table->smallInteger('parent_id')->nullable();
                $table->timestamps();
                $table->smallInteger('is_active')->default(1);
                $table->smallInteger('code')->nullable()->unique();
                $table->smallInteger('rank')->nullable();
                $table->string('parent_short_code')->nullable();
                $table->index('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = [
            'pgsql_bandhan',
            'pgsql_sbi',
            'pgsql_ifms',
            'pgsql_ifms_v3'
        ];

        foreach ($connections as $connection) {
            // Using raw statement with CASCADE to handle any dependent objects
            DB::connection($connection)->statement('DROP TABLE IF EXISTS codemasters CASCADE');
        }
    }
};
