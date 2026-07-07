<?php

namespace App\Services\DynamicForm\Contracts;

interface FieldTypeInterface
{
    /**
     * Render the form field HTML.
     *
     * @param array $fieldConfig
     * @param mixed $value
     * @return \Illuminate\View\View|string
     */
    public function render(array $fieldConfig, $value = null);

    /**
     * Get validation rules for this field.
     *
     * @param array $fieldConfig
     * @return array|string
     */
    public function rules(array $fieldConfig);
}
