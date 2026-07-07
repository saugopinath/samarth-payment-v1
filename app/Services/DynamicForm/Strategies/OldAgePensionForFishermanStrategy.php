<?php

namespace App\Services\DynamicForm\Strategies;

use App\Services\DynamicForm\Builder\FormBuilder;

class OldAgePensionForFishermanStrategy implements SchemeStrategyInterface
{
    public function buildForm(FormBuilder $builder): void
    {
        // TODO: Define fields specific to OLD AGE PENSION FOR FISHERMAN
          $builder
            ->addStep('Personal Information')
                ->addField('first_name', 'First Name', 'text', ['is_required' => true])
                ->addField('last_name', 'Last Name', 'text', ['is_required' => true])
                ->addField('dob', 'Date of Birth', 'text', ['is_required' => true, 'placeholder' => 'YYYY-MM-DD'])
                ->addField('gender', 'Gender', 'select', [
                    'is_required' => true,
                    'options' => ['Male' => 'Male', 'Female' => 'Female']
                ])
            
            ->addStep('Income Details')
                ->addField('annual_income', 'Annual Income', 'text', ['is_required' => true])
                ->addField('source_of_income', 'Source of Income', 'select', [
                    'options' => ['Pension' => 'Pension', 'Agriculture' => 'Agriculture', 'Other' => 'Other']
                ])
            
            ->addStep('Bank Details')
                ->addField('account_number', 'Account Number', 'text', ['is_required' => true])
                ->addField('ifsc_code', 'IFSC Code', 'text', ['is_required' => true]);
    }
}
