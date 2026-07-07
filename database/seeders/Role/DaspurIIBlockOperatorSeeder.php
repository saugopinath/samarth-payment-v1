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
class DaspurIIBlockOperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::findByName('Operator');
        $office = OfficeMaster::where('district_id',318)->where('block_id',2979)->first();
        $scheme = Scheme::where('short_name','LB')->first();
        $c_time=Carbon::now()->format('Y/m/d H:i:s');
        $password_expires_at= Carbon::now()->addDays(intval(Config::get('app.password_expire_day')))->format('Y/m/d H:i:s');
        $user = User::create([
            'name' => 'DaspurIIBlockOperator',
            'mobile_no' => '8583035694',
            'email' => 'blockdaspur2@gmail.com',
            'password' => Hash::make('1234'),
            'password_set_time' => $c_time, 
            'password_expires_at' => $password_expires_at, 
        ]);

        UserPersonal::create([
            'user_id' => $user->id,
            'name' => $user->name,
        ]);
        $user->assignRole($role);
        $user_office = UserRoleSchemeOfficeMapping::create([
            'user_id' =>  $user->id,
            'scheme_id' => $scheme->id,
            'role_id' => $role->id,
            'office_id' => $office->id,
        ]);

        
       
    }
}
