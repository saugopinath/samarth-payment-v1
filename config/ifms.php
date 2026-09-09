<?php

return [
    'paths' => [
        'xmlpush' => env('IFMS_EPAYMENT_FILES_XMLPUSH', 'ePayment_Files_006'),
        'dotdone' => env('IFMS_EPAYMENT_FILES_DOTDONE', 'ePayment_Files_002'),
        'ack' => env('IFMS_EPAYMENT_FILES_ACK', 'ePayment_Files_002/ACK/'),
        'wrong' => env('IFMS_EPAYMENT_FILES_WRONG', 'ePayment_Files_003'),
        'response' => env('IFMS_EPAYMENT_FILES_RESPONSE', 'ePayment_Files_005'),
    ],
    'local_paths' => [
        'pushed' => env('IFMS_LOCAL_PATH_PUSHED', 'ifms_xml/pushed'),
        'ack' => env('IFMS_LOCAL_PATH_ACK', 'ifms_xml/ack'),
        'rbi_resp' => env('IFMS_LOCAL_PATH_RBI_RESP', 'ifms_xml/rbi_resp'),
        'ifms_resp' => env('IFMS_LOCAL_PATH_IFMS_RESP', 'ifms_xml/ifms_resp')
        ],
];
