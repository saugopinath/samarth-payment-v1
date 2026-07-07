<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterTab;

class MasterTabSeeder extends Seeder
{
    public function run(): void
    {
        $tabs = [
            [
                'tab_name' => 'Personal Details',
                'tab_code' => 101,
                'tab_short_name' => 'personal_details',
                'tab_model_name' => 'BeneficiaryPersonalDetail',
                'tab_icon' => 'M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z',
                'is_active' => true,
            ],
            [
                'tab_name' => 'Contact Details',
                'tab_code' => 102,
                'tab_short_name' => 'contact_details',
                'tab_model_name' => 'BeneficiaryContactDetail',
                'tab_icon' => 'M14.25 9.75v-4.5m0 4.5h4.5m-4.5 0 6-6m-3 18c-8.284 0-15-6.716-15-15V4.5A2.25 2.25 0 0 1 4.5 2.25h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173l-1.293.97a1.062 1.062 0 0 0-.38 1.21 12.035 12.035 0 0 0 7.143 7.143c.441.162.928-.004 1.21-.38l.97-1.293a1.125 1.125 0 0 1 1.173-.417l4.423 1.106c.5.125.852.575.852 1.091V19.5a2.25 2.25 0 0 1-2.25 2.25h-2.25Z',
                'is_active' => true,
            ],
            [
                'tab_name' => 'Bank Details',
                'tab_code' => 103,
                'tab_short_name' => 'bank_details',
                'tab_model_name' => 'BeneficiaryBankDetail',
                'tab_icon' => 'M2 10L12 3l10 7v2H2v-2zm1 3h2v6H3v-6zm4 0h2v6H7v-6zm4 0h2v6h-2v-6zm4 0h2v6h-2v-6zm4 0h2v6h-2v-6zM2 20h20v1H2v-1z',
                'is_active' => true,
            ],
            [
                'tab_name' => 'Encloser Details',
                'tab_code' => 104,
                'tab_short_name' => 'encloser_details',
                'tab_model_name' => 'EnclosureDetail',
                'tab_icon' => 'm18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13',
                'is_active' => true,
            ],
            [
                'tab_name' => 'Self Declaration',
                'tab_code' => 105,
                'tab_short_name' => 'self_declaration',
                'tab_model_name' => 'BeneficiarySelfDeclaration',
                'tab_icon' => 'M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z',
                'is_active' => true,
            ],
            [
                'tab_name' => 'Personal Identification',
                'tab_code' => 106,
                'tab_short_name' => 'personal_identification',
                'tab_model_name' => 'BeneficiaryPersonalIdentification',
                'tab_icon' => 'M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z',
                'is_active' => true,
            ],
            [
                'tab_name' => 'Land Details',
                'tab_code' => 107,
                'tab_short_name' => 'land_details',
                'tab_model_name' => 'BeneficiaryLandDetail',
                'tab_icon' => 'M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z',
                'is_active' => true,
            ],
        ];

        foreach ($tabs as $tab) {
            MasterTab::updateOrCreate(
                ['tab_code' => $tab['tab_code']],
                $tab
            );
        }
    }
}
