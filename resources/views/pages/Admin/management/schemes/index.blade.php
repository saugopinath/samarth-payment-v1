<?php use function Laravel\Folio\{name, middleware}; name('management.schemes'); middleware(['auth', 'verified']); ?>
<?php

use App\Models\Scheme;
use App\Models\Department;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $schemeId = null;
    public $name = '';
    public $short_name = '';
    public $department_id = null;
    public $min_age = null;
    public $max_age = null;
    public $base_amount = null;
    public $description = '';
    public $is_active = true;
    public $allow_entry = true;
    public $allow_verification = true;
    public $allow_approval = true;
    public $isModalOpen = false;

    public function editScheme($id)
    {
        $this->resetValidation();
        $scheme = Scheme::findOrFail($id);
        $this->schemeId = $scheme->id;
        $this->name = $scheme->name;
        $this->short_name = $scheme->short_name;
        $this->department_id = $scheme->department_id;
        $this->min_age = $scheme->min_age;
        $this->max_age = $scheme->max_age;
        $this->base_amount = $scheme->base_amount;
        $this->description = $scheme->description;
        $this->is_active = $scheme->is_active ?? true;
        $this->allow_entry = $scheme->allow_entry ?? true;
        $this->allow_verification = $scheme->allow_verification ?? true;
        $this->allow_approval = $scheme->allow_approval ?? true;
        
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['schemeId', 'name', 'short_name', 'department_id', 'min_age', 'max_age', 'base_amount', 'description', 'is_active', 'allow_entry', 'allow_verification', 'allow_approval']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'min_age' => 'nullable|integer|min:0|max:150',
            'max_age' => 'nullable|integer|min:0|max:150|gte:min_age',
            'base_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'allow_entry' => 'boolean',
            'allow_verification' => 'boolean',
            'allow_approval' => 'boolean',
        ], [
            'max_age.gte' => 'The maximum age must be greater than or equal to the minimum age.'
        ]);

        $scheme = Scheme::findOrFail($this->schemeId);
        $scheme->update([
            'name' => $this->name,
            'short_name' => $this->short_name,
            'department_id' => $this->department_id,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'base_amount' => $this->base_amount,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'allow_entry' => $this->allow_entry,
            'allow_verification' => $this->allow_verification,
            'allow_approval' => $this->allow_approval,
        ]);

        session()->flash('success', 'Scheme configuration updated successfully!');
        $this->closeModal();
    }

    public function with(): array
    {
        return [
            'schemes' => Scheme::with('Department')->latest()->paginate(10),
            'departments' => Department::orderBy('name')->get(),
        ];
    }
}; ?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Scheme Configuration') }}
        </h2>
    </x-slot>

    @volt
    <div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session()->has('success'))
                <div class="mb-4 bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-slate-900 dark:text-slate-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Manage Schemes</h3>
                        <a href="{{ route('management.schemes.onboard') }}" wire:navigate class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Onboard New Scheme
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-700/50">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">ID</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Department</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Min Age</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Max Age</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse($schemes as $scheme)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/25 transition-colors">
                                        <td class="px-4 py-3 text-sm">{{ $scheme->id }}</td>
                                        <td class="px-4 py-3 text-sm font-medium">
                                            {{ $scheme->name }} 
                                            @if(!($scheme->is_active ?? true))
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800">
                                                    Inactive
                                                </span>
                                            @endif
                                            <br><span class="text-xs text-slate-500">{{ $scheme->short_name }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $scheme->Department?->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $scheme->min_age ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $scheme->max_age ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <button wire:click="editScheme({{ $scheme->id }})" class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 font-medium">Edit Config</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No schemes found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Modal -->
                    <div x-data="{ open: @entangle('isModalOpen') }" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
                        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 max-h-[90vh] flex flex-col">
                            
                            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Edit Scheme Configuration</h3>
                                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors rounded-full p-1 hover:bg-slate-200 dark:hover:bg-slate-700">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <form wire:submit="save" class="flex flex-col overflow-hidden flex-1">
                                <div class="p-6 overflow-y-auto">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <x-input-label for="name" :value="__('Scheme Name')" />
                                            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required />
                                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="short_name" :value="__('Short Code')" />
                                            <x-text-input wire:model="short_name" id="short_name" class="block mt-1 w-full" type="text" required />
                                            <x-input-error :messages="$errors->get('short_name')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="department_id" :value="__('Department')" />
                                            <select wire:model="department_id" id="department_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 dark:focus:border-amber-600 focus:ring-amber-500 dark:focus:ring-amber-600 rounded-md shadow-sm">
                                                <option value="">No Department</option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="min_age" :value="__('Min Age')" />
                                            <x-text-input wire:model="min_age" id="min_age" class="block mt-1 w-full" type="number" />
                                            <x-input-error :messages="$errors->get('min_age')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="max_age" :value="__('Max Age')" />
                                            <x-text-input wire:model="max_age" id="max_age" class="block mt-1 w-full" type="number" />
                                            <x-input-error :messages="$errors->get('max_age')" class="mt-2" />
                                        </div>

                                        <div>
                                            <x-input-label for="base_amount" :value="__('Base Amount')" />
                                            <x-text-input wire:model="base_amount" id="base_amount" class="block mt-1 w-full" type="number" step="0.01" />
                                            <x-input-error :messages="$errors->get('base_amount')" class="mt-2" />
                                        </div>

                                        <div class="md:col-span-2">
                                            <x-input-label for="description" :value="__('Description')" />
                                            <textarea wire:model="description" id="description" class="block mt-1 w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 dark:focus:border-amber-600 focus:ring-amber-500 dark:focus:ring-amber-600 rounded-md shadow-sm" rows="3"></textarea>
                                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                        </div>

                                        <div class="md:col-span-2 mt-4 border-t border-slate-100 dark:border-slate-700 pt-4">
                                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Scheme Capabilities</h4>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Active Status</span>
                                                </label>
                                                
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" wire:model="allow_entry" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Allow Entry</span>
                                                </label>

                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" wire:model="allow_verification" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Allow Verification</span>
                                                </label>

                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" wire:model="allow_approval" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Allow Approval</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-end gap-3 mt-auto">
                                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                                        <span wire:loading wire:target="save" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        {{ $schemes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endvolt
</x-app-layout>
