<?php

// 1. Read sbi.php childs and payment_lot.php
$sbiData = include('d:\Projects\samarth-payment-v1\config\sbi.php');
$sbiChilds = $sbiData['childs'] ?? [];

$paymentLotPath = 'd:\Projects\samarth-payment-v1\config\payment_lot.php';
$paymentLotContent = file_get_contents($paymentLotPath);

// Generate string for childs
$childsStr = "";
foreach ($sbiChilds as $child) {
    $childsStr .= "        array(\n";
    foreach ($child as $key => $value) {
        $childsStr .= "            \"$key\" => \"$value\",\n";
    }
    $childsStr .= "        ),\n";
}

// Replace 'childs' => [ ... ] in payment_lot.php
$paymentLotContent = preg_replace(
    '/\'childs\'\s*=>\s*\[\s*\]/s', 
    "'childs' => [\n" . $childsStr . "    ]", 
    $paymentLotContent
);
file_put_contents($paymentLotPath, $paymentLotContent);

// 2. Remove sbi merging logic from CodemasterSeeder.php
$seederPath = 'd:\Projects\samarth-payment-v1\database\seeders\CodemasterSeeder.php';
$seederContent = file_get_contents($seederPath);

$seederContent = preg_replace('/\$sbiChilds = config\(\'sbi\.childs\'\);\s*if \(is_array\(\$sbiChilds\)\) \{\s*\$codemasterChilds = array_merge\(\$codemasterChilds, \$sbiChilds\);\s*\}/s', '', $seederContent);

file_put_contents($seederPath, $seederContent);

// 3. Delete sbi.php
unlink('d:\Projects\samarth-payment-v1\config\sbi.php');

echo "OK";
