<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = Illuminate\Support\Facades\Storage::get('cert_enc/sbi-public-key.pem');
var_dump($key);

$res = openssl_get_publickey($key);
var_dump($res);
if (!$res) {
    echo openssl_error_string();
}
