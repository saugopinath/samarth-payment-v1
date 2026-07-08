<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LGD\StateSeeder::class,
            LGD\DistrictSeeder::class,
            LGD\BlockSeeder::class,
            LGD\PanchayatSeeder::class,
            LGD\SubdivisionSeeder::class,
            LGD\MunicipalitiesSeeder::class,
            LGD\WardSeeder::class,
            Bank\BankSeeder::class,
            Bank\IfscSeeder::class,
            DepartmentSeeder::class,
            CodemasterSeeder::class,
            SchemeSeeder::class,
            Role\RolePermissionSeeder::class,
            OfficeMaster\RoleOfficeTypeSeeder::class,
            OfficeMaster\OfficeMastersTableSeeder::class,
            Role\SuperAdminSeeder::class,
            Role\WbHodSeeder::class,
            Role\PaschimMedinipurApproverSeeder::class,
            Role\DaspurIIBlockVerifierSeeder::class,
            Role\DaspurIIBlockOperatorSeeder::class,
            Role\GhatalSdoOperatorSeeder::class,
            Role\GhatalSdoVerifierSeeder::class,
            ValidationFailedCodemasterSeeder::class,
            UpdateNextLevelRoleIdSeeder::class,
            OpTypeSeeder::class,
            DsPhaseSeeder::class,
            FinancialYearSeeder::class,
            MonthSeeder::class,


        ]);
    }
}
