<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:wipe {--database=} {--drop-views} {--drop-types} {--force}', function () {
    $this->info("Dropping all tables safely to bypass PostgreSQL lock limits...");
    
    $connection = $this->option('database') ?: config('database.default');
    $dbName = config("database.connections.{$connection}.database");

    if (config("database.connections.{$connection}.driver") === 'pgsql') {
        config(["database.connections.{$connection}.database" => 'postgres']);
        DB::purge($connection);
        DB::reconnect($connection);
        
        try {
            DB::connection($connection)->statement("
                SELECT pg_terminate_backend(pid) 
                FROM pg_stat_activity 
                WHERE datname = '{$dbName}' 
                AND pid <> pg_backend_pid()
            ");
            DB::connection($connection)->statement("DROP DATABASE IF EXISTS \"{$dbName}\"");
            DB::connection($connection)->statement("CREATE DATABASE \"{$dbName}\"");
        } catch (\Exception $e) {
            $this->error('Failed to wipe database: ' . $e->getMessage());
            return 1;
        }

        config(["database.connections.{$connection}.database" => $dbName]);
        DB::purge($connection);
        DB::reconnect($connection);
    } else {
        // Fallback for non-postgres
        DB::connection($connection)->getSchemaBuilder()->dropAllTables();
    }
    
    $this->info("Dropped all tables successfully.");
})->purpose('Drop all tables safely bypassing postgres lock limits');
