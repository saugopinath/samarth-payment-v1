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
class GhatalSdoOperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c_time=Carbon::now()->format('Y/m/d H:i:s');
        $password_expires_at= Carbon::now()->addDays(intval(Config::get('app.password_expire_day')))->format('Y/m/d H:i:s');
        $role = Role::findByName('Operator');
        $office = OfficeMaster::where('district_id',318)->where('subdivision_id',34401)->first();
        $scheme = Scheme::where('short_name','LB')->first();
    
        $user = User::create([
            'name' => 'GhatalSDOOperator',
            'mobile_no' => '8583039586',
            'email' => 'sdoghataloperator@gmail.com',
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
