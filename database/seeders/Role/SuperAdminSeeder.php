<?php

namespace Database\Seeders\Role;

use App\Models\OfficeMaster;
use App\Models\Role;
use App\Models\Scheme;
use App\Models\User;
use App\Models\UserPersonal;
use App\Models\UserRoleSchemeOfficeMapping;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c_time = Carbon::now()->format('Y/m/d H:i:s');
        $password_expires_at = Carbon::now()->addDays(intval(Config::get('app.password_expire_day')))->format('Y/m/d H:i:s');
        $role = Role::findByName('Super Admin');
        $office = OfficeMaster::where('state_id', 19)->where('district_id', null)->where('office_type_id', 151)->first();
        $scheme = Scheme::where('short_name', 'LB')->first();
        // $rolesArr = ['name' => 'Super Admin', 'guard_name' => 'web', 'created_at' => now(),'updated_at' => now()];

        $user = User::create([
            'name' => 'Admin',
            'mobile_no' => '8583035693',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('1234'),
            'password_set_time' => $c_time,
            'password_expires_at' => $password_expires_at,
        ]);

        UserPersonal::create([
            'user_id' => $user->id,
            'name' => $user->name,
        ]);

        app(PermissionRegistrar::class)
            ->setPermissionsTeamId(
                $scheme->id
            );

        // $role = Role::findByName('Super Admin');
        $user->assignRole($role);
        $user_office = UserRoleSchemeOfficeMapping::create([
            'user_id' => $user->id,
            'scheme_id' => $scheme->id,
            'role_id' => $role->id,
            'office_id' => $office->id,
        ]);
    }
}