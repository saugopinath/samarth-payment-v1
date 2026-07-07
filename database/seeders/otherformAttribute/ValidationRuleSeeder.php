<?php

namespace Database\Seeders\OtherformAttribute;

use App\Models\ValidationRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ValidationRuleSeeder  extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['rule' => 'required', 'description' => 'Field is mandatory'],
            ['rule' => 'nullable', 'description' => 'Field is optional'],
            ['rule' => 'email',    'description' => 'Must be valid email'],
            ['rule' => 'numeric',  'description' => 'Must be numeric'],
            ['rule' => 'max:255',  'description' => 'Maximum 255 characters'],
            ['rule' => 'min:3',    'description' => 'Minimum 3 characters'],
        ];
        foreach ($rules as $rule) {
            ValidationRule::updateOrCreate(
                ['rule' => $rule['rule']],
                ['description' => $rule['description']]
            );
        }
    }
}
