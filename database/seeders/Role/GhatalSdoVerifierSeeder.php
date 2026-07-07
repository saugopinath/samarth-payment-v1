<?php

namespace Database\Seeders\Role;

use App\Models\Role;
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
class GhatalSdoVerifierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c_time=Carbon::now()->format('Y/m/d H:i:s');
        $password_expires_at= Carbon::now()->addDays(intval(Config::get('app.password_expire_day')))->format('Y/m/d H:i:s');
        $role_verifier = Role::findByName('Verifier');
        $role_delegated_verifier = Role::findByName('Delegated Verifier');
        $office = OfficeMaster::where('district_id',318)->where('subdivision_id',34401)->first();
        $scheme = Scheme::where('short_name','LB')->first();
    
        $user_verifier = User::create([
            'name' => 'GhatalSDOVerifier',
            'mobile_no' => '8583035697',
            'email' => 'sdoghatalverifier@gmail.com',
            'password' => Hash::make('1234'),
            'password_set_time' => $c_time, 
            'password_expires_at' => $password_expires_at, 
        ]);

        UserPersonal::create([
            'user_id' => $user_verifier->id,
            'name' => $user_verifier->name,
        ]);
        $user_verifier->assignRole($role_verifier);
        $user_office = UserRoleSchemeOfficeMapping::create([
            'user_id' =>  $user_verifier->id,
            'scheme_id' => $scheme->id,
            'role_id' => $role_verifier->id,
            'office_id' => $office->id,
        ]);

        $user_delegated_verifier = User::create([
            'name' => 'GhatalSDODelegatedVerifier',
            'mobile_no' => '8583035698',
            'email' => 'sdoghataldelegatedverifier@gmail.com',
            'password' => Hash::make('1234'),
            'password_set_time' => $c_time, 
            'password_expires_at' => $password_expires_at, 
        ]);

        UserPersonal::create([
            'user_id' => $user_delegated_verifier->id,
            'name' => $user_delegated_verifier->name,
        ]);
        $user_delegated_verifier->assignRole($role_delegated_verifier);
        $user_office = UserRoleSchemeOfficeMapping::create([
            'user_id' =>  $user_delegated_verifier->id,
            'scheme_id' => $scheme->id,
            'role_id' => $role_delegated_verifier->id,
            'office_id' => $office->id,
        ]);
       
    }
}
