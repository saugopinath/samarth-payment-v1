<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = file_get_contents(storage_path('app/cert_enc/sbi-public-key.pem'));
$aesKey = openssl_random_pseudo_bytes(32);
$success = openssl_public_encrypt($aesKey, $encryptedAesKey, $key, OPENSSL_PKCS1_OAEP_PADDING);
var_dump($success);
if (!$success) {
    echo openssl_error_string() . "\n";
}
