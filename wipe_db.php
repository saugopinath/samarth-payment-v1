<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$dbName = env('DB_DATABASE', 'samarth-payment-v11');

config(['database.connections.pgsql.database' => 'postgres']);
Illuminate\Support\Facades\DB::purge('pgsql');
Illuminate\Support\Facades\DB::reconnect('pgsql');

Illuminate\Support\Facades\DB::statement("
    SELECT pg_terminate_backend(pid) 
    FROM pg_stat_activity 
    WHERE datname = '{$dbName}' 
    AND pid <> pg_backend_pid()
");

Illuminate\Support\Facades\DB::statement("DROP DATABASE IF EXISTS \"{$dbName}\"");
Illuminate\Support\Facades\DB::statement("CREATE DATABASE \"{$dbName}\"");

echo "Database {$dbName} recreated successfully.\n";
