<?php use function Laravel\Folio\{name, middleware}; name('management.departments'); middleware(['auth', 'verified']); ?>
<?php

use App\Models\Department;
use App\Models\State;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $departmentId = null;
    public $name = '';
    public $short_name = '';
    public $state_id = null;
    
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $departmentToDelete = null;

    public function createDepartment()
    {
        $this->resetValidation();
        $this->reset(['departmentId', 'name', 'short_name', 'state_id']);
        // Default to WEST BENGAL (19) if desired, or leave null
        $this->state_id = 19;
        $this->isModalOpen = true;
    }

    public function editDepartment($id)
    {
        $this->resetValidation();
        $department = Department::findOrFail($id);
        $this->departmentId = $department->id;
        $this->name = $department->name;
        $this->short_name = $department->short_name;
        $this->state_id = $department->state_id;
        
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['departmentId', 'name', 'short_name', 'state_id']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'state_id' => 'required|exists:states,id',
        ]);

        if ($this->departmentId) {
            $department = Department::findOrFail($this->departmentId);
            $department->update([
                'name' => $this->name,
                'short_name' => $this->short_name,
                'state_id' => $this->state_id,
            ]);
            session()->flash('success', 'Department updated successfully!');
        } else {
            Department::create([
                'name' => $this->name,
                'short_name' => $this->short_name,
                'state_id' => $this->state_id,
            ]);
            session()->flash('success', 'Department created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->departmentToDelete = $id;
        $this->isDeleteModalOpen = true;
    }

    public function deleteDepartment()
    {
        if ($this->departmentToDelete) {
            $department = Department::find($this->departmentToDelete);
            if ($department) {
                $department->delete();
                session()->flash('success', 'Department deleted successfully!');
            }
        }
        $this->isDeleteModalOpen = false;
        $this->departmentToDelete = null;
    }

    public function with(): array
    {
        return [
            'departments' => Department::with('State')->orderBy('name')->paginate(10),
            'states' => State::orderBy('name')->get(),
        ];
    }
}; ?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Departments') }}
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
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Manage Departments</h3>
                        <button wire:click="createDepartment" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Department
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-700/50">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600 w-16">ID</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Short Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">State</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse($departments as $dept)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/25 transition-colors">
                                        <td class="px-4 py-3 text-sm">{{ $dept->id }}</td>
                                        <td class="px-4 py-3 text-sm font-medium">{{ $dept->name }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $dept->short_name }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $dept->State?->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <button wire:click="editDepartment({{ $dept->id }})" class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 font-medium mr-3">Edit</button>
                                            <button wire:click="confirmDelete({{ $dept->id }})" class="text-sm text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 font-medium">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No departments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $departments->links() }}
                    </div>

                    <!-- Create/Edit Modal -->
                    <div x-data="{ open: @entangle('isModalOpen') }" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
                        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-lg w-full mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col">
                            
                            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $departmentId ? 'Edit Department' : 'Add Department' }}</h3>
                                <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors rounded-full p-1 hover:bg-slate-200 dark:hover:bg-slate-700">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <form wire:submit="save" class="flex flex-col overflow-hidden">
                                <div class="p-6">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <x-input-label for="name" :value="__('Department Name')" />
                                            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required />
                                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="short_name" :value="__('Short Code')" />
                                            <x-text-input wire:model="short_name" id="short_name" class="block mt-1 w-full" type="text" required />
                                            <x-input-error :messages="$errors->get('short_name')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="state_id" :value="__('State')" />
                                            <select wire:model="state_id" id="state_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 dark:focus:border-amber-600 focus:ring-amber-500 dark:focus:ring-amber-600 rounded-md shadow-sm" required>
                                                <option value="">Select a State</option>
                                                @foreach($states as $state)
                                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('state_id')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-end gap-3 mt-auto">
                                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                                        <span wire:loading wire:target="save" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div x-data="{ open: @entangle('isDeleteModalOpen') }" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
                        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 p-6">
                            
                            <div class="flex items-center gap-4 mb-4 text-rose-600 dark:text-rose-500">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Confirm Deletion</h3>
                            </div>
                            
                            <p class="text-slate-600 dark:text-slate-400 mb-6">Are you sure you want to delete this department? This action cannot be undone.</p>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                    Cancel
                                </button>
                                <button type="button" wire:click="deleteDepartment" class="px-4 py-2 text-sm font-medium bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                                    <span wire:loading wire:target="deleteDepartment" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endvolt
</x-app-layout>
