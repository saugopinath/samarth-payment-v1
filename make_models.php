<?php

$models = [
    'BenPaymentDetail' => ['table' => 'ben_payment_details', 'connection' => 'pgsql_payment'],
    'BenMonthwisePaymentStatus' => ['table' => 'ben_monthwise_payment_status', 'connection' => 'pgsql_payment'],
    'PaymentLotMaster' => ['table' => 'payment_lot_master', 'connection' => 'pgsql_payment'],
    'ValidationLotMaster' => ['table' => 'validation_lot_master', 'connection' => 'pgsql_payment'],
    'BankCodemaster' => ['table' => 'codemasters', 'connection' => null],
    'BandhanTransactionLotDetail' => ['table' => 'bandhan_transaction_lot_details', 'connection' => 'pgsql_bandhan'],
    'BandhanValidationLotDetail' => ['table' => 'bandhan_validation_lot_details', 'connection' => 'pgsql_bandhan'],
    'SbiTransactionLotDetail' => ['table' => 'sbi_transaction_lot_details', 'connection' => 'pgsql_sbi'],
    'SbiValidationLotDetail' => ['table' => 'sbi_validation_lot_details', 'connection' => 'pgsql_sbi'],
    'IfmsTransactionLotDetail' => ['table' => 'ifms_transaction_lot_details', 'connection' => 'pgsql_ifms'],
    'IfmsValidationLotMasterAdditionalInfo' => ['table' => 'ifms_validation_lot_master_additional_info', 'connection' => 'pgsql_ifms'],
    'IfmsV3TransactionLotDetail' => ['table' => 'ifms_v3_transaction_lot_details', 'connection' => 'pgsql_ifms_v3'],
    'BenPaymentAbpsDetail' => ['table' => 'ben_payment_abps_details', 'connection' => 'pgsql_payment'],
    'BenPaymentAccDetail' => ['table' => 'ben_payment_acc_details', 'connection' => 'pgsql_payment'],
    'FailedPaymentDetail' => ['table' => 'failed_payment_details', 'connection' => 'pgsql_payment'],
];

foreach ($models as $model => $props) {
    echo "Creating $model...\n";
    exec("php artisan make:model $model");
    
    $path = "app/Models/$model.php";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $insert = "\n";
        if ($props['connection']) {
            $insert .= "    protected \$connection = '{$props['connection']}';\n";
        }
        $insert .= "    protected \$table = '{$props['table']}';\n";
        $insert .= "    protected \$guarded = [];\n";
        
        $content = preg_replace('/(class '.$model.' extends Model\n\{)/', '$1' . $insert, $content);
        file_put_contents($path, $content);
        echo "Updated $model with connection and table.\n";
    }
}
echo "Done.\n";
