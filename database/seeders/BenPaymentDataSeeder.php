<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Block;
use App\Models\Subdivision;
use App\Models\Scheme;
use App\Models\Ifsccodemaster;
use App\Models\BenPaymentDetail;
use App\Models\BenPaymentAbpsDetail;
use App\Models\BenPaymentAccDetail;

class BenPaymentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        
        $schemeIds = Scheme::pluck('id')->toArray();
        if (empty($schemeIds)) {
            $schemeIds = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 13, 17, 19, 20, 21];
        }

        $blocks = Block::all();
        $subdivisions = Subdivision::all();
        
        $ifscMasters = Ifsccodemaster::with('bankmaster')->inRandomOrder()->take(200)->get();
        if ($ifscMasters->isEmpty()) {
            $ifscMasters = collect([(object)['code' => 'SBIN0000001', 'bankmaster' => (object)['bank_code' => 'SBIN']]]);
        }

        // 1. Seed Payment Details per Scheme and Block
        foreach ($schemeIds as $schemeId) {
            foreach ($blocks as $block) {
                $benPaymentDetails = [];
                $benPaymentAbpsDetails = [];
                $benPaymentAccDetails = [];
                
                for ($i = 0; $i < 10; $i++) {
                    $benId = $faker->unique()->numberBetween(10000000, 79999999);
                    $aadharNo = $faker->numerify('############');
                    $accNo = $faker->numerify('###########');
                    
                    $ifscMaster = $ifscMasters->random();
                    $ifsc = $ifscMaster->code;
                    $npci = $ifscMaster->bankmaster ? $ifscMaster->bankmaster->bank_code : substr($ifsc, 0, 4);

                    $benPaymentDetails[] = [
                        'ben_id' => $benId,
                        'scheme_id' => $schemeId,
                        'ben_name' => $faker->name,
                        'last_accno' => $accNo,
                        'last_ifsc' => $ifsc,
                        'npci_bank_code' => $npci,
                        'aadhar_no' => $aadharNo,
                        'is_eligible' => true,
                        'created_by_dist_code' => $block->district_id ?? 1, // Removed clone, primitive integer
                        'created_by_sdo_code' => 0, // Fallback for blocks
                        'created_by_block_code' => $block->id,
                    ];

                   
                }
                
                try {
                    foreach ($benPaymentDetails as $detail) {
                        $model = new BenPaymentDetail();
                        $model->fill($detail);
                        $model->save();
                    }
                } catch (\Exception $e) {
                    $this->command->error("Error inserting Block records for Scheme ID {$schemeId}: " . $e->getMessage());
                }
                //BenPaymentAbpsDetail::insert($benPaymentAbpsDetails);
               // BenPaymentAccDetail::insert($benPaymentAccDetails);
            }
            $this->command->info("Inserted Block records for Scheme ID: {$schemeId}");
        }

        // 2. Seed Payment Details per Scheme and Subdivision
        foreach ($schemeIds as $schemeId) {
            foreach ($subdivisions as $subdivision) {
                $benPaymentDetails = [];
                $benPaymentAbpsDetails = [];
                $benPaymentAccDetails = [];
                
                for ($i = 0; $i < 10; $i++) {
                    $benId = $faker->unique()->numberBetween(80000000, 99999999);
                    $aadharNo = $faker->numerify('############');
                    $accNo = $faker->numerify('###########');
                    
                    $ifscMaster = $ifscMasters->random();
                    $ifsc = $ifscMaster->code;
                    $npci = $ifscMaster->bankmaster ? $ifscMaster->bankmaster->bank_code : substr($ifsc, 0, 4);

                    $benPaymentDetails[] = [
                        'ben_id' => $benId,
                        'scheme_id' => $schemeId,
                        'ben_name' => $faker->name,
                        'last_accno' => $accNo,
                        'last_ifsc' => $ifsc,
                        'npci_bank_code' => $npci,
                        'aadhar_no' => $aadharNo,
                        'is_eligible' => true,
                        'created_by_dist_code' => $subdivision->district_id ?? 1,
                        'created_by_sdo_code' => $subdivision->id ?? 1,
                        'created_by_block_code' => 0, // Fallback for subdivisions
                    ];

                    
                }
                try {
                    foreach ($benPaymentDetails as $detail) {
                        $model1 = new BenPaymentDetail();
                        $model1->fill($detail);
                        $model1->save();
                    }
                } catch (\Exception $e) {
                    $this->command->error("Error inserting Subdivision records for Scheme ID {$schemeId}: " . $e->getMessage());
                }
                //BenPaymentAbpsDetail::insert($benPaymentAbpsDetails);
                //BenPaymentAccDetail::insert($benPaymentAccDetails);
            }
            $this->command->info("Inserted Subdivision records for Scheme ID: {$schemeId}");
        }
        
    }
}
