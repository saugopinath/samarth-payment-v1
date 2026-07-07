<?php

namespace App\Models;

class AgeManagements extends BaseAuditableModel
{
    protected $guarded = [];

    protected $casts = [
        'special_case' => 'array',
    ];

    /**
     * Get dynamic age validation rule
     */
    public function getAgeValidationRule(): string
    {
        // Special case has priority
        if ($this->is_special && !empty($this->special_case)) {

            $min = $this->special_case['min'] ?? $this->min_age;
            $max = $this->special_case['max'] ?? $this->max_age;

            return "required|integer|min:$min|max:$max";
        }

        // Normal case
        return "required|integer|min:$this->min_age|max:$this->max_age";
    }

    /**
     * Age limit for frontend display
     */
    public function getAgeLimit(): array
    {
        return [
            'min' => $this->min_age,
            'max' => $this->max_age,
        ];
    }

    /**
     * Check if special age rule exists
     */
    public function hasSpecialCase(): bool
    {
        return (bool) $this->is_special;
    }
}
