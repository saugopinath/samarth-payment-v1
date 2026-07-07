<?php

namespace App\Services\DynamicForm;

use App\Services\DynamicForm\Contracts\FieldTypeInterface;
use App\Services\DynamicForm\Types\TextField;
use App\Services\DynamicForm\Types\SelectField;
use InvalidArgumentException;

class FieldFactory
{
    /**
     * Create an instance of the appropriate FieldTypeInterface based on the type string.
     *
     * @param string $type
     * @return FieldTypeInterface
     * @throws InvalidArgumentException
     */
    public static function make(string $type): FieldTypeInterface
    {
        switch (strtolower($type)) {
            case 'text':
            case 'string':
            case 'email':
            case 'number':
                return new TextField();
            case 'select':
            case 'dropdown':
                return new SelectField();
            default:
                // Default to text field if unknown, or throw exception based on strictness
                return new TextField();
        }
    }
}
