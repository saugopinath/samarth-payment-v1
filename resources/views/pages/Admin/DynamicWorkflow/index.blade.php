<?php
use function Laravel\Folio\{name, middleware};
use App\Models\DynamicWorkflowModule;
use Livewire\Volt\Component;
use Livewire\WithPagination;

name('DynamicWorkflow.index');
middleware(['auth']);

new class extends Component {
    use WithPagination;

    public function toggleActive($id)
    {
        $workflow = DynamicWorkflowModule::findOrFail($id);
        $workflow->is_active = !$workflow->is_active;
        $workflow->save();
        session()->flash('message', 'Workflow status updated successfully.');
    }

    public function with(): array
    {
        return [
            'workflows' => DynamicWorkflowModule::latest()->paginate(10),
        ];
    }
};
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Dynamic Workflow List') }}
        </h2>
    </x-slot>

    @volt
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if (session()->has('message'))
                    <div class="mb-4 bg-emerald-100 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg relative" role="alert">
                        <span class="block sm:inline font-medium">{{ session('message') }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Workflows</h3>
                    <a href="{{ route('DynamicWorkflow.wizard') }}" wire:navigate class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                        Add New Workflow
                    </a>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                                <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">ID</th>
                                <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Code</th>
                                <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Name</th>
                                <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Created At</th>
                                <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 text-right">Active</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($workflows as $workflow)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $workflow->id }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-200">{{ $workflow->module_code }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-200">
                                        {{ $workflow->module_name }}
                                        @if(!$workflow->is_active)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $workflow->created_at->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <label class="inline-flex items-center cursor-pointer" title="Toggle Active Status">
                                            <input type="checkbox" wire:click="toggleActive({{ $workflow->id }})" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50 h-5 w-5" {{ $workflow->is_active ? 'checked' : '' }}>
                                        </label>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <p>No workflows found. Create one to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $workflows->links() }}
                </div>
            </div>
        </div>
    </div>
    @endvolt
</x-app-layout>
