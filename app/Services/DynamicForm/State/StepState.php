<?php

namespace App\Services\DynamicForm\State;

class StepState implements FormStateInterface
{
    protected FormStateContext $context;
    
    protected string $title;
    protected array $fields;
    protected int $index;
    
    protected ?FormStateInterface $nextState = null;
    protected ?FormStateInterface $previousState = null;

    public function __construct(string $title, array $fields, int $index)
    {
        $this->title = $title;
        $this->fields = $fields;
        $this->index = $index;
    }

    public function setContext(FormStateContext $context): void
    {
        $this->context = $context;
    }

    public function setNextState(FormStateInterface $state): void
    {
        $this->nextState = $state;
    }

    public function setPreviousState(FormStateInterface $state): void
    {
        $this->previousState = $state;
    }

    public function next(): void
    {
        if ($this->nextState) {
            $this->context->transitionTo($this->nextState);
        }
    }

    public function previous(): void
    {
        if ($this->previousState) {
            $this->context->transitionTo($this->previousState);
        }
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isLastStep(): bool
    {
        return $this->nextState === null;
    }

    public function isFirstStep(): bool
    {
        return $this->previousState === null;
    }
    
    public function getIndex(): int
    {
        return $this->index;
    }

    public function getNextState(): ?FormStateInterface
    {
        return $this->nextState;
    }

    public function getPreviousState(): ?FormStateInterface
    {
        return $this->previousState;
    }
}
