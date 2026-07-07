<?php

namespace Database\Seeders\OfficeMaster;

use Illuminate\Database\Seeder;
use App\Models\RoleOfficeTypeMapping;
use App\Models\Role;
use App\Models\Codemaster;
class RoleOfficeTypeSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
         $state_office_type=Codemaster::where('code',151)->first();
         $district_office_type=Codemaster::where('code',152)->first();
         $block_office_type=Codemaster::where('code',153)->first();
         $sdo_office_type=Codemaster::where('code',154)->first();
         $role_hod = Role::findByName('HOD');
         $role_delegated_hod = Role::findByName('Delegated HOD');
         $role_approver = Role::findByName('Approver');
         $role_delegated_approver = Role::findByName('Delegated Approver');
         $role_verifier = Role::findByName('Verifier');
         $role_delegated_verifier = Role::findByName('Delegated Verifier');
         $role_operator = Role::findByName('Operator');
         $list = array(
            array(
                'role_id' => $role_hod->id,
                'office_type_id' => $state_office_type->code
            ),
           
             array(
                'role_id' => $role_delegated_hod->id,
                'office_type_id' => $state_office_type->code
            ),
             array(
                'role_id' => $role_approver->id,
                'office_type_id' => $district_office_type->code
            ),
           
             array(
                'role_id' => $role_delegated_approver->id,
                'office_type_id' => $district_office_type->code
            ),
            array(
                'role_id' => $role_verifier->id,
                'office_type_id' => $block_office_type->code
            ),
           
             array(
                'role_id' => $role_delegated_verifier->id,
                'office_type_id' => $block_office_type->code
            ),
            array(
                'role_id' => $role_verifier->id,
                'office_type_id' => $sdo_office_type->code
            ),
           
             array(
                'role_id' => $role_delegated_verifier->id,
                'office_type_id' => $sdo_office_type->code
            ),
            array(
                'role_id' => $role_operator->id,
                'office_type_id' => $block_office_type->code
            ),
             array(
                'role_id' => $role_operator->id,
                'office_type_id' => $sdo_office_type->code
            )
            
            
        );
        foreach ($list as $item) {
            RoleOfficeTypeMapping::create([
                'role_id' => $item['role_id'],
                'office_type_id' => $item['office_type_id']
            ]);
        }
    }
        
      
        
        
    
}