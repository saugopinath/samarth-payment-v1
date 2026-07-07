<?php

namespace App\Services\DynamicForm\Types;

use App\Services\DynamicForm\Contracts\FieldTypeInterface;

class TextField implements FieldTypeInterface
{
    public function render(array $fieldConfig, $value = null)
    {
        // $fieldConfig contains attributes like name, label, placeholder, etc.
        $name = $fieldConfig['name'] ?? 'text_field';
        $label = $fieldConfig['label'] ?? 'Text Field';
        $placeholder = $fieldConfig['placeholder'] ?? '';
        $isRequired = $fieldConfig['is_required'] ?? false;
        
        $requiredAttr = $isRequired ? 'required' : '';
        $asterisk = $isRequired ? '<span class="text-rose-500">*</span>' : '';
        
        // We will output a Blade-compatible string or render a view.
        // For simplicity, let's return HTML string with Livewire model binding.
        
        return <<<HTML
        <div class="mb-4">
            <label for="{$name}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {$label} {$asterisk}
            </label>
            <input type="text" id="{$name}" wire:model.live="formData.{$name}" placeholder="{$placeholder}" {$requiredAttr}
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
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
        $rules[] = 'string';
        
        if (!empty($fieldConfig['max_length'])) {
            $rules[] = 'max:' . $fieldConfig['max_length'];
        }
        
        return $rules;
    }
}
