<?php
DB::enableQueryLog();
$detail = [
    [
        'ben_id' => 99999999,
        'scheme_id' => 1,
        'ben_name' => 'Test Insert',
        'ben_status' => 1,
        'created_by_dist_code' => 1,
        'created_by_sdo_code' => 1,
        'created_by_block_code' => 1,
    ]
];
$res = App\Models\BenPaymentDetail::insert($detail);
print_r($res);
print_r(DB::getQueryLog());
