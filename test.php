<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lots = \App\Models\PaymentLotMaster::whereIn('lot_no', [16,17,18])->get(['lot_no', 'payment_mode', 'scheme_id', 'lot_type_id'])->toArray();
file_put_contents('lots_output.json', json_encode($lots, JSON_PRETTY_PRINT));
echo "Done.";
