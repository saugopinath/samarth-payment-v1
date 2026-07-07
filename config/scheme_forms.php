<?php

return [
    'default' => [
        'tabs' => [
            [
                'id' => 'personal_details',
                'label' => 'Personal Details',
                'component' => 'beneficiary.tabs.personal-details'
            ],
            [
                'id' => 'identification_numbers',
                'label' => 'Identification Numbers',
                'component' => 'beneficiary.tabs.identification-numbers'
            ],
            [
                'id' => 'contact_details',
                'label' => 'Contact Details',
                'component' => 'beneficiary.tabs.contact-details'
            ],
            [
                'id' => 'bank_account_details',
                'label' => 'Bank Account Details',
                'component' => 'beneficiary.tabs.bank-details'
            ],
            [
                'id' => 'enclosure_list',
                'label' => 'Enclosure List',
                'component' => 'beneficiary.tabs.enclosure-list'
            ],
            [
                'id' => 'self_declaration',
                'label' => 'Self Declaration',
                'component' => 'beneficiary.tabs.self-declaration'
            ]
        ]
    ]
];
