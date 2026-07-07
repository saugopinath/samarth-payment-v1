<?php

namespace App\Services\DynamicForm\Builder;

class FormStep
{
    public string $name;
    public array $fields = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Add a field to this step.
     */
    public function addField(string $name, string $label, string $type = 'text', array $options = []): self
    {
        $fieldConfig = array_merge([
            'name' => $name,
            'label' => $label,
            'type' => $type,
        ], $options);

        $this->fields[] = $fieldConfig;
        
        return $this;
    }
}
