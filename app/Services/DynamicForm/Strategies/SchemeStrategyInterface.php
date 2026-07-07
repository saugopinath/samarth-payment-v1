<?php

namespace App\Services\DynamicForm\Strategies;

use App\Services\DynamicForm\Builder\FormBuilder;

interface SchemeStrategyInterface
{
    /**
     * Build the form specific to this scheme using the FormBuilder.
     */
    public function buildForm(FormBuilder $builder): void;
}
