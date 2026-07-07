<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Beneficiary Entry') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="w-full px-2 sm:px-4 lg:px-6">
            <livewire:beneficiary.entry-wizard :schemeId="$schemeId" />
        </div>
    </div>
</x-app-layout>
