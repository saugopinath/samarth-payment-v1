<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Lot Master Status Codes
    |--------------------------------------------------------------------------
    |
    | This file maintains the hardcoded status codes for payment lots across
    | different payment agencies (SBI, IFMS, Bandhan) and common statuses.
    |
    */

    'parent_status_code' => [
        'common'  => 'PLSC',
        'sbi'     =>  'PLSSBI',
        'ifms'    =>  'PLSIFMS',
        'bandhan' =>  'PLSBANDHAN',
    ],

    'status' => [
        'common' => [
            'not_generated' => 'PLSCNG',
            'generated'     => 'PLSCG',
            'push'          => 'PLSCP',
            'success'       => 'PLSCS',
            'failed'        => 'PLSCF',
            'defunct'       => 'PLSCDFC',
        ],
        'sbi' => [
            'signed'               => 'PLSSBISIGN',
            'pushed'               => 'PLSSBIPUSH',
            'ack'                  => 'PLSSBIACK',
            'response'             => 'PLSSBIRES',
        ],
        'ifms' => [
            'ack'                  => 'PLSIFMSACK',
            'response'             => 'PLSIFMSRES',
            'treasury'             => 'PLSIFMSTreasury',
            'rbi'                  => 'PLSIFMSRBI',
            'all_action_done'      => 'PLSIFMSAllActionDone',
        ],
        'bandhan' => [
            // Additional statuses for Bandhan to be added here
        ],
    ]
];
