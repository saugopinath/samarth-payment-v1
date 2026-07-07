<?php
use function Laravel\Folio\{name, middleware};

name('DynamicWorkflow.wizard');
middleware(['auth']);
?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Dynamic Workflow') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:dynamic-workflow.workflow-wizard />
        </div>
    </div>
</x-app-layout>
