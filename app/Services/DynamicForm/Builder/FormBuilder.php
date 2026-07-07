<?php

namespace App\Services\DynamicForm\Builder;

use App\Services\DynamicForm\State\StepState;
use App\Services\DynamicForm\State\FormStateInterface;

class FormBuilder
{
    public array $steps = [];
    protected ?FormStep $currentStep = null;

    /**
     * Start a new step in the form.
     */
    public function addStep(string $name): self
    {
        // Save the previous step if exists
        if ($this->currentStep) {
            $this->steps[] = $this->currentStep;
        }
        
        $this->currentStep = new FormStep($name);
        return $this;
    }

    /**
     * Add a field to the current step.
     */
    public function addField(string $name, string $label, string $type = 'text', array $options = []): self
    {
        if (!$this->currentStep) {
            throw new \Exception("Cannot add field without calling addStep() first.");
        }
        
        $this->currentStep->addField($name, $label, $type, $options);
        return $this;
    }

    /**
     * Build the linked list of states and return the initial state.
     */
    public function build(): ?FormStateInterface
    {
        if ($this->currentStep && !in_array($this->currentStep, $this->steps, true)) {
            $this->steps[] = $this->currentStep;
        }

        if (empty($this->steps)) {
            return null;
        }

        /** @var StepState[] $states */
        $states = [];
        
        // Create all state objects
        foreach ($this->steps as $index => $step) {
            $states[] = new StepState($step->name, $step->fields, $index + 1);
        }
        
        // Link them together
        $count = count($states);
        for ($i = 0; $i < $count; $i++) {
            if ($i > 0) {
                $states[$i]->setPreviousState($states[$i - 1]);
            }
            if ($i < $count - 1) {
                $states[$i]->setNextState($states[$i + 1]);
            }
        }

        // Return the first state
        return $states[0];
    }
}
