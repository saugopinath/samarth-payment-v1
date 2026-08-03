<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::connection('pgsql_payment')->statement("DROP TABLE IF EXISTS sbi.payment_lot_master_additional_info CASCADE");
echo "Dropped table sbi.payment_lot_master_additional_info";
