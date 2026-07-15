<?php

$detail = [
    'ben_id' => 12345678,
    'scheme_id' => 1,
    'ben_name' => 'Test',
    'ben_status' => 1,
    'created_by_dist_code' => 1,
    'created_by_sdo_code' => 1,
    'created_by_block_code' => 1,
];
$model = new App\Models\BenPaymentDetail();
$model->fill($detail);
print_r($model->getAttributes());
print_r($model->isDirty());
try {
    DB::enableQueryLog();
    $res = $model->save();
    print_r($res);
    print_r(DB::getQueryLog());
} catch (\Exception $e) {
    print_r($e->getMessage());
}
