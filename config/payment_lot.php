<?php

$parent_status_code = [
    'common'  => 'payment_lot_status_common',
    'sbi'     =>  'payment_lot_status_sbi',
    'ifms'    =>  'payment_lot_status_ifms',
    'bandhan' =>  'payment_lot_status_bandhan',
];

$status = [
    'common' => [
        'not_generated' => 'PLSCNG',
        'generated'     => 'PLSCG',
        'push'          => 'PLSCP',
        'ack'           => 'PLSCACK',
        'response'      => 'PLSCRES',
        'success'       => 'PLSCS',
        'failed'        => 'PLSCF',
        'defunct'       => 'PLSCDFC',
    ],
    'sbi' => [
        'signed'               => 'PLSSBISIGN',
    ],
    'ifms' => [
        'treasury'             => 'PLSIFMSTreasury',
        'rbi'                  => 'PLSIFMSRBI',
        'all_action_done'      => 'PLSIFMSAllActionDone',
    ]
    
];

$configuration_codes = [
    'create_enable_disable' => '52301',
    'push_enable_disable' => '52302',
    'response_enable_disable' => '52303',
];

$lot_types = [
    'regular' => '5901',
    'arrear'  => '5902',
];

$payment_modes = [
    'sbi' => '5201',
    'ifms' => '5202',
    'ifms_v3' => '5203',
    'bandhan' => '5204',
];

$payment_types = [
    'acc_base' => '5001',
    'abps_base' => '5002',
];

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

    'parent_status_code' => $parent_status_code,

    'status' => $status,

    'configuration_codes' => $configuration_codes,

    'lot_types' => $lot_types,

    'payment_modes' => $payment_modes,

    'payment_types' => $payment_types,

    'childs' => [
        // Configuration Options
        array(
            "name" => "Enable/Disable Payment Lot Create Option",
            "short_name" => "payment_lot_create_enable_disable",
            "parent_short_code" => "payment_lot_configuration",
             "code" => $configuration_codes['create_enable_disable'],
        ),
        array(
            "name" => "Enable/Disable Payment Lot Push Option",
            "short_name" => "payment_lot_push_enable_disable",
            "parent_short_code" => "payment_lot_configuration",
             "code" => $configuration_codes['push_enable_disable'],
        ),
        array(
            "name" => "Enable/Disable Payment Lot Response Option",
            "short_name" => "payment_lot_response_enable_disable",
            "parent_short_code" => "payment_lot_configuration",
             "code" => $configuration_codes['response_enable_disable'],
        ),
        
        // Payment Types
        array(
            "name" => "Account Base",
            "short_name" => "acc_base",
            "parent_short_code" => "payment_type",
            "code" => $payment_types['acc_base'],
        ),
        array(
            "name" => "ABPS Base",
            "short_name" => "abps_base",
            "parent_short_code" => "payment_type",
            "code" => $payment_types['abps_base'],
        ),
        
        // Lot Types
        array(
            "name" => "Regular Lot",
            "short_name" => "regular_lot",
            "parent_short_code" => "lot_type",
            "code" => $lot_types['regular'],
        ),
        array(
            "name" => "Arrer Lot",
            "short_name" => "arrer_lot",
            "parent_short_code" => "lot_type",
            "code" => $lot_types['arrear'],
        ),
        
        // Payment Modes
        array(
            "name" => "SBI",
            "short_name" => "sbi",
            "parent_short_code" => "payment_mode",
            "code" => $payment_modes['sbi'],
        ),
        array(
            "name" => "IFMS",
            "short_name" => "ifms",
            "parent_short_code" => "payment_mode",
            "code" => $payment_modes['ifms'],
        ),
        array(
            "name" => "IFMS V3",
            "short_name" => "ifms-v3",
            "parent_short_code" => "payment_mode",
            "code" => $payment_modes['ifms_v3'],
        ),
        array(
            "name" => "Bandhan",
            "short_name" => "Bandhan",
            "parent_short_code" => "payment_mode",
            "code" => $payment_modes['bandhan'],
        ),
        
        // Common Statuses
        array(
            "name" => "Not Generated",
            "short_name" => "not_generated",
            "parent_short_code" => $parent_status_code['common'],
            "code" => $status['common']['not_generated'],
        ),
        array(
            "name" => "Generated",
            "short_name" => "generated",
            "parent_short_code" => $parent_status_code['common'],
            "code" => $status['common']['generated'],
        ),
        array(
            "name" => "Push",
            "short_name" => "push",
            "parent_short_code" => $parent_status_code['common'],
            "code" => $status['common']['push'],
        ),
        array(
            "name" => "Ack",
            "short_name" => "ack",
            "parent_short_code" => $parent_status_code['common'],
            "code" => $status['common']['ack'],
        ),
         array(
            "name" => "Response",
            "short_name" => "response",
            "parent_short_code" => $parent_status_code['common'],
            "code" => $status['common']['response'],
        ),
        array(
            "name" => "Success",
            "short_name" => "success",
            "parent_short_code" => $parent_status_code['common'],
            "code" => $status['common']['success'],
        ),
        array(
            "name" => "Failed",
            "short_name" => "failed",
            "parent_short_code" => $parent_status_code['common'],
            "code" => $status['common']['failed'],
        ),
        array(
            "name" => "Defunct",
            "short_name" => "defunct",
            "parent_short_code" => $parent_status_code['common'],
            "code" => $status['common']['defunct'],
        ),
        
        // SBI Statuses
        array(
            "name" => "Signed",
            "short_name" => "sbi_signed",
            "parent_short_code" => $parent_status_code['sbi'],
            "code" => $status['sbi']['signed'],
        ),
      
      
      
        
        // IFMS Statuses
      
        array(
            "name" => "Treasury",
            "short_name" => "ifms_treasury",
            "parent_short_code" => $parent_status_code['ifms'],
            "code" => $status['ifms']['treasury'],
        ),
        array(
            "name" => "RBI",
            "short_name" => "ifms_rbi",
            "parent_short_code" => $parent_status_code['ifms'],
            "code" => $status['ifms']['rbi'],
        ),
        array(
            "name" => "All Action Done",
            "short_name" => "ifms_all_action_done",
            "parent_short_code" => $parent_status_code['ifms'],
            "code" => $status['ifms']['all_action_done'],
        )
        
       
    ],
];

