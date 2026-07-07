<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Scheme;
use App\Services\DynamicForm\FieldFactory;
use App\Services\DynamicForm\Strategies\SchemeStrategyFactory;
use App\Services\DynamicForm\Builder\FormBuilder;
use App\Services\DynamicForm\State\FormStateContext;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

name('DynamicForm.index');
middleware(['auth']);

new class extends Component {
    public $schemeId = null;
    public $schemes = [];
    
    // State management for Livewire (stateless between requests)
    public $currentStepIndex = 1;
    public $formData = [];
    
    #[On('scheme-selected')]
    public function handleSchemeSelection($schemeId)
    {
        $this->schemeId = $schemeId;
        $this->currentStepIndex = 1;
        $this->formData = [];
    }
    
    /**
     * Dynamically builds and retrieves the Form State Machine based on the chosen scheme.
     * 
     * @return FormStateContext|null
     */
    public function getStateContextProperty(): ?FormStateContext
    {
        if (!$this->schemeId) {
            return null;
        }
        
        // 1. Strategy Pattern: Get the specific form builder logic for the scheme
        $strategy = SchemeStrategyFactory::make($this->schemeId);
        
        // 2. Builder Pattern: Construct the form structure dynamically
        $builder = new FormBuilder();
        $strategy->buildForm($builder);
        $initialState = $builder->build();
        
        if (!$initialState) {
            return null;
        }

        // 3. State Pattern: Initialize state machine context
        $context = new FormStateContext();
        $context->transitionTo($initialState);
        
        // Fast-forward to the user's current step (since Livewire is stateless)
        $context->jumpToStep($this->currentStepIndex);
        
        return $context;
    }
    
    public function nextStep()
    {
        $this->validateCurrentStep();
        
        $context = $this->stateContext;
        if ($context && !$context->getCurrentState()->isLastStep()) {
            $this->currentStepIndex++;
        }
    }
    
    public function previousStep()
    {
        if ($this->currentStepIndex > 1) {
            $this->currentStepIndex--;
        }
    }
    
    protected function validateCurrentStep()
    {
        $context = $this->stateContext;
        if (!$context || !$context->getCurrentState()) {
            return;
        }
        
        $rules = [];
        $messages = [];
        $fields = $context->getCurrentState()->getFields();
        
        foreach ($fields as $fieldConfig) {
            $type = $fieldConfig['type'] ?? 'text';
            $fieldStrategy = FieldFactory::make($type);
            $fieldRules = $fieldStrategy->rules($fieldConfig);
            $rules["formData.{$fieldConfig['name']}"] = $fieldRules;
            $messages["formData.{$fieldConfig['name']}.required"] = "The {$fieldConfig['label']} field is required.";
        }
        
        if (!empty($rules)) {
            $this->validate($rules, $messages);
        }
    }
    
    public function submitForm()
    {
        $this->validateCurrentStep();
        
        // Save the $this->formData to the database or perform the action
        Log::info('Dynamic form submitted successfully using State pattern.', ['scheme_id' => $this->schemeId, 'data' => $this->formData]);
        
        session()->flash('success', 'Form submitted successfully!');
        
        // Reset state
        $this->formData = [];
        $this->currentStepIndex = 1;
        $this->schemeId = null;
    }
    
    // Render helper for Blade
    public function renderField($fieldConfig)
    {
        $type = $fieldConfig['type'] ?? 'text';
        $fieldStrategy = FieldFactory::make($type);
        return $fieldStrategy->render($fieldConfig, $this->formData[$fieldConfig['name']] ?? null);
    }
};
?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Dynamic Multi-Step Form') }}
        </h2>
    </x-slot>

    @volt
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-xl sm:rounded-xl border border-slate-200 dark:border-slate-700">
                @if(!$schemeId)
                    <livewire:scheme-selector />
                @endif

                @if($schemeId && $this->stateContext)
                    @php
                        $context = $this->stateContext;
                        $currentState = $context->getCurrentState();
                        $allStates = $context->getAllStates();
                        $totalSteps = count($allStates);
                        $selectedSchemeModel = \App\Models\Scheme::find($schemeId);
                    @endphp
                    
                    <!-- Selected Scheme Header / Back Button -->
                    <div class="px-6 py-4 sm:px-10 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ $selectedSchemeModel?->display_name ?? $selectedSchemeModel?->name ?? 'Scheme '.$schemeId }} Application
                            </h3>
                        </div>
                        <button type="button" wire:click="$set('schemeId', null)" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Change Scheme
                        </button>
                    </div>
                    <div class="p-6 sm:p-10">
                        <!-- Progress Indicator -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between">
                                @foreach($allStates as $idx => $stateObj)
                                    @php $i = $idx + 1; @endphp
                                    <div class="flex flex-col items-center flex-1 relative">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold z-10 transition-colors {{ $currentStepIndex >= $i ? 'bg-amber-500 text-white shadow-md' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                                            @if($currentStepIndex > $i)
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            @else
                                                {{ $i }}
                                            @endif
                                        </div>
                                        <div class="mt-2 text-xs font-medium text-center {{ $currentStepIndex >= $i ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500' }}">
                                            {{ $stateObj->getTitle() }}
                                        </div>
                                        
                                        @if($i < $totalSteps)
                                            <div class="absolute top-5 left-1/2 w-full h-0.5 -mt-px -z-0 {{ $currentStepIndex > $i ? 'bg-amber-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Flash Message -->
                        @if (session()->has('success'))
                            <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-lg flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="font-medium">{{ session('success') }}</span>
                            </div>
                        @endif

                        <!-- Step Content -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 min-h-[300px]">
                            <h4 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-700 pb-2">
                                {{ $currentState->getTitle() }}
                            </h4>
                            
                            @if(!$currentState->isLastStep())
                                <div class="space-y-4">
                                    @foreach($currentState->getFields() as $fieldConfig)
                                        {!! $this->renderField($fieldConfig) !!}
                                        @error('formData.'.$fieldConfig['name']) 
                                            <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> 
                                        @enderror
                                    @endforeach
                                </div>
                            @else
                                <!-- Final Review Step -->
                                <div class="space-y-4">
                                    <p class="text-slate-600 dark:text-slate-400 mb-4">Please review your information before submitting.</p>
                                    <div class="bg-white dark:bg-slate-900 rounded-lg p-4 shadow-sm border border-slate-200 dark:border-slate-700">
                                        <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                                            @foreach($formData as $key => $value)
                                                <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                    <dt class="text-sm font-medium leading-6 text-slate-900 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                                    <dd class="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-400 sm:col-span-2 sm:mt-0">{{ $value }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 flex items-center justify-between">
                            <button type="button" wire:click="previousStep" 
                                    class="px-5 py-2.5 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-medium shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors {{ $currentStepIndex == 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $currentStepIndex == 1 ? 'disabled' : '' }}>
                                Back
                            </button>
                            
                            @if(!$currentState->isLastStep())
                                <button type="button" wire:click="nextStep" 
                                        class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium shadow-sm transition-colors flex items-center gap-2">
                                    Next Step
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            @else
                                <button type="button" wire:click="submitForm" 
                                        class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium shadow-sm transition-colors flex items-center gap-2">
                                    Submit Form
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @elseif($schemeId)
                    <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p>No schema configuration found for this scheme.</p>
                    </div>
                @endif
                
            </div>
        </div>
    </div>
    @endvolt
</x-app-layout>
