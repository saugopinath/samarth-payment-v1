<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(\App\Services\PaymentLotXmlService::class);
    $lotMaster = \App\Models\PaymentLotMaster::where('lot_no', '6')->firstOrFail();
    $result = $service->generateAndSignXml($lotMaster);
    echo "SUCCESS\n";
    print_r($result);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
