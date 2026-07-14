<?php

namespace Database\Seeders\Role;

use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\UserPersonal;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Scheme;
use App\Models\OfficeMaster;
use App\Models\UserRoleSchemeOfficeMapping;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
class PurbaMedinipurApproverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c_time=Carbon::now()->format('Y/m/d H:i:s');
        $password_expires_at= Carbon::now()->addDays(intval(Config::get('app.password_expire_day')))->format('Y/m/d H:i:s');
        $role_approver = Role::findByName('Approver');
        $role_delegated_approver = Role::findByName('Delegated Approver');
        $office = OfficeMaster::where('district_id',318)->where('office_type_id',152)->first();
        $scheme = Scheme::where('short_name','LB')->first();
    
        $user_approver = User::create([
            'name' => 'purbamedinipurapprover',
            'mobile_no' => '7583035699',
            'email' => 'approverdpurbamedinipur@gmail.com',
            'password' => Hash::make('1234'),
            'password_set_time' => $c_time, 
            'password_expires_at' => $password_expires_at, 
        ]);

        UserPersonal::create([
            'user_id' => $user_approver->id,
            'name' => $user_approver->name,
        ]);
//        $user_approver->assignRole($role_approver);
        $user_office = UserRoleSchemeOfficeMapping::create([
            'user_id' =>  $user_approver->id,
            'scheme_id' => $scheme->id,
            'role_id' => $role_approver->id,
            'office_id' => $office->id,
        ]);
         $user_delegated_approver = User::create([
            'name' => 'purbamedinipurdelegatedapprover',
            'mobile_no' => '7583035700',
            'email' => 'delegatedapproverdpurbamedinipur@gmail.com',
            'password' => Hash::make('1234'),
            'password_set_time' => $c_time, 
            'password_expires_at' => $password_expires_at, 
        ]);

        UserPersonal::create([
            'user_id' => $user_delegated_approver->id,
            'name' => $user_delegated_approver->name,
        ]);
//        $user_delegated_approver->assignRole($role_delegated_approver);
        $user_office = UserRoleSchemeOfficeMapping::create([
            'user_id' =>  $user_delegated_approver->id,
            'scheme_id' => $scheme->id,
            'role_id' => $role_delegated_approver->id,
            'office_id' => $office->id,
        ]);
       
    }
}
