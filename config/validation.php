<?php

return [
    'childs' => [
        array(
            "name" => "Not Generated",
            "short_name" => "validation_lot_status_not_generated",
            "parent_short_code" => "validation_lot_status",
            "code" => "52201",
        ),
        array(
            "name" => "Generated but yet not Pushed",
            "short_name" => "validation_lot_status_generated",
            "parent_short_code" => "validation_lot_status",
            "code" => "52202",
        ),
        array(
            "name" => "Generated and Pushed but Response Pending",
            "short_name" => "validation_lot_status_generated_and_pushed",
            "parent_short_code" => "validation_lot_status",
            "code" => "52203",
        ),
        array(
            "name" => "Generated,Pushed and Response Received",
            "short_name" => "validation_lot_status_generated_pushed_response_received",
            "parent_short_code" => "validation_lot_status",
            "code" => "52204",
        ),
        array(
            "name" => "Enable/Disable Validation Lot Create Option",
            "short_name" => "validation_lot_create_enable_disable",
            "parent_short_code" => "validation_lot_configuration",
            "code" => "52401",
        ),
        array(
            "name" => "Enable/Disable Validation Lot Push Option",
            "short_name" => "validation_lot_push_enable_disable",
            "parent_short_code" => "validation_lot_configuration",
            "code" => "52402",
        ),
        array(
            "name" => "Enable/Disable Validation Lot Response Option",
            "short_name" => "validation_lot_response_enable_disable",
            "parent_short_code" => "validation_lot_configuration",
            "code" => "52403",
        ),
        array(
            "name" => "Account Validation",
            "short_name" => "acc_base_validation",
            "parent_short_code" => "validation_mode",
            "code" => "52501",
        ),
        array(
            "name" => "DBT Validation",
            "short_name" => "dbt_validation",
            "parent_short_code" => "validation_mode",
            "code" => "52502",
        ),
    ]
];
