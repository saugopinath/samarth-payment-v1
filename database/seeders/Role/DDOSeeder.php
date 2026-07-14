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
class DDOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c_time=Carbon::now()->format('Y/m/d H:i:s');
        $password_expires_at= Carbon::now()->addDays(intval(Config::get('app.password_expire_day')))->format('Y/m/d H:i:s');
        $role = Role::findByName('DDO');
        $office = OfficeMaster::where('office_type_id',151)->first();
        $scheme = Scheme::where('short_name','LB')->first();
    
        $user_hod = User::updateOrCreate(
            ['mobile_no' => '9733596960'],
            [
                'name' => 'wbddo',
                'email' => 'wbddo@gmail.com',
                'password' => Hash::make('1234'),
                'password_set_time' => $c_time, 
                'password_expires_at' => $password_expires_at, 
            ]
        );

        UserPersonal::updateOrCreate(
            ['user_id' => $user_hod->id],
            ['name' => $user_hod->name]
        );
//        $user_hod->assignRole($role_hod);
        $user_office = UserRoleSchemeOfficeMapping::updateOrCreate(
            [
                'user_id' =>  $user_hod->id,
                'scheme_id' => $scheme->id,
                'role_id' => $role->id,
                'office_id' => $office->id,
            ]
        );

        $role_delegated_hod = Role::findByName('Delegated DDO');

         $user_delegated_hod = User::updateOrCreate(
            ['mobile_no' => '8583035702'],
            [
                'name' => 'wbdelegatedhod',
                'email' => 'wbdelegatedhod@gmail.com',
                'password' => Hash::make('1234'),
                'password_set_time' => $c_time, 
                'password_expires_at' => $password_expires_at, 
            ]
        );

        UserPersonal::updateOrCreate(
            ['user_id' => $user_delegated_hod->id],
            ['name' => $user_delegated_hod->name]
        );
//        $user_delegated_hod->assignRole($role_delegated_hod);
        $user_office = UserRoleSchemeOfficeMapping::updateOrCreate([
            'user_id' =>  $user_delegated_hod->id,
            'scheme_id' => $scheme->id,
            'role_id' => $role_delegated_hod->id,
            'office_id' => $office->id,
        ]);
       
    }
}
