<?php

namespace App\Services\DynamicForm\Types;

use App\Services\DynamicForm\Contracts\FieldTypeInterface;

class SelectField implements FieldTypeInterface
{
    public function render(array $fieldConfig, $value = null)
    {
        $name = $fieldConfig['name'] ?? 'select_field';
        $label = $fieldConfig['label'] ?? 'Select Field';
        $isRequired = $fieldConfig['is_required'] ?? false;
        $options = $fieldConfig['options'] ?? [];
        
        $requiredAttr = $isRequired ? 'required' : '';
        $asterisk = $isRequired ? '<span class="text-rose-500">*</span>' : '';
        
        $optionsHtml = '<option value="">-- Select --</option>';
        if (is_array($options)) {
            foreach ($options as $optValue => $optLabel) {
                // If it's a simple flat array vs associative
                if (is_int($optValue)) {
                    $optValue = $optLabel;
                }
                $optionsHtml .= "<option value=\"{$optValue}\">{$optLabel}</option>";
            }
        }
        
        return <<<HTML
        <div class="mb-4">
            <label for="{$name}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {$label} {$asterisk}
            </label>
            <select id="{$name}" wire:model.live="formData.{$name}" {$requiredAttr}
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                {$optionsHtml}
            </select>
        </div>
HTML;
    }

    public function rules(array $fieldConfig)
    {
        $rules = [];
        if (!empty($fieldConfig['is_required'])) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        // Additional rules can be added here
        return $rules;
    }
}
