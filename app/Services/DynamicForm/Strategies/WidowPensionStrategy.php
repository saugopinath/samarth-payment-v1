<?php

namespace App\Services\DynamicForm\Strategies;

use App\Services\DynamicForm\Builder\FormBuilder;

class WidowPensionStrategy implements SchemeStrategyInterface
{
    public function buildForm(FormBuilder $builder): void
    {
        $builder
            ->addStep('Applicant Information')
                ->addField('first_name', 'First Name', 'text', ['is_required' => true])
                ->addField('last_name', 'Last Name', 'text', ['is_required' => true])
                ->addField('gender', 'Gender', 'select', [
                    'is_required' => true,
                    'options' => ['Female' => 'Female']
                ])
            
            ->addStep('Spouse Details')
                ->addField('spouse_name', 'Late Spouse Name', 'text', ['is_required' => true])
                ->addField('date_of_death', 'Date of Death', 'text', ['is_required' => true, 'placeholder' => 'YYYY-MM-DD'])
                ->addField('death_certificate_no', 'Death Certificate No.', 'text', ['is_required' => true])
            
            ->addStep('Bank Details')
                ->addField('account_number', 'Account Number', 'text', ['is_required' => true])
                ->addField('ifsc_code', 'IFSC Code', 'text', ['is_required' => true]);
    }
}
