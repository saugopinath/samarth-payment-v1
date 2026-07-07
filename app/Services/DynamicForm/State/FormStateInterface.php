<?php

namespace App\Services\DynamicForm\State;

interface FormStateInterface
{
    /**
     * Set the context for this state.
     */
    public function setContext(FormStateContext $context): void;

    /**
     * Proceed to the next state.
     */
    public function next(): void;

    /**
     * Go back to the previous state.
     */
    public function previous(): void;

    /**
     * Get the current step's fields configuration.
     */
    public function getFields(): array;

    /**
     * Get the title of the current step.
     */
    public function getTitle(): string;

    /**
     * Check if this is the last step.
     */
    public function isLastStep(): bool;

    /**
     * Check if this is the first step.
     */
    public function isFirstStep(): bool;
    
    /**
     * Get the step index.
     */
    public function getIndex(): int;

    /**
     * Get the next state object.
     */
    public function getNextState(): ?FormStateInterface;

    /**
     * Get the previous state object.
     */
    public function getPreviousState(): ?FormStateInterface;
}
