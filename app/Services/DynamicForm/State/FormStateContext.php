<?php

namespace App\Services\DynamicForm\State;

class FormStateContext
{
    private ?FormStateInterface $currentState = null;

    public function transitionTo(FormStateInterface $state): void
    {
        $this->currentState = $state;
        $this->currentState->setContext($this);
    }

    public function getCurrentState(): ?FormStateInterface
    {
        return $this->currentState;
    }

    public function next(): void
    {
        if ($this->currentState) {
            $this->currentState->next();
        }
    }

    public function previous(): void
    {
        if ($this->currentState) {
            $this->currentState->previous();
        }
    }

    /**
     * Fast-forward the state machine to a specific step index.
     * Useful for stateless environments like Livewire.
     */
    public function jumpToStep(int $index): void
    {
        // Go to start
        while ($this->currentState && !$this->currentState->isFirstStep()) {
            $this->currentState->previous();
        }
        
        // Traverse to index
        while ($this->currentState && $this->currentState->getIndex() < $index) {
            $this->currentState->next();
        }
    }

    /**
     * Get all states to render progress bars, etc.
     */
    public function getAllStates(): array
    {
        $states = [];
        $tempState = $this->currentState;
        
        if (!$tempState) return [];

        // Go to start safely without mutating currentState
        while ($tempState && !$tempState->isFirstStep()) {
            $tempState = $tempState->getPreviousState();
        }
        
        // Traverse forward and collect all states
        while ($tempState) {
            $states[] = clone $tempState; // clone or just return reference
            $tempState = $tempState->getNextState();
        }

        return $states;
    }
}
