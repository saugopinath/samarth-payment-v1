<?php

use function Laravel\Folio\{name, middleware};
use Spatie\Permission\Models\Permission;
use Livewire\Volt\Component;
use Livewire\WithPagination;

name('management.permissions');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public string $name = '';
    public $permissionId = null;
    public bool $isModalOpen = false;
    public bool $is_active = true;

    public function rules()
    {
        $uniqueRule = 'unique:permissions,name';
        if ($this->permissionId) {
            $uniqueRule .= ',' . $this->permissionId;
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'is_active' => 'boolean',
        ];
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('name', 'permissionId', 'is_active');

        if ($id) {
            $permission = Permission::findOrFail($id);
            $this->permissionId = $permission->id;
            $this->name = $permission->name;
            $this->is_active = $permission->is_active ?? true;
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
        $this->reset('name', 'permissionId', 'is_active');
    }

    public function save()
    {
        $this->validate();

        if ($this->permissionId) {
            $permission = Permission::findOrFail($this->permissionId);
            $permission->update([
                'name' => $this->name,
                'is_active' => $this->is_active
            ]);
            session()->flash('message', 'Permission updated successfully.');
        } else {
            Permission::create([
                'name' => $this->name,
                'is_active' => $this->is_active
            ]);
            session()->flash('message', 'Permission created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Permission::findOrFail($id)->delete();
        session()->flash('message', 'Permission deleted successfully.');
    }

    public function with(): array
    {
        return [
            'permissions' => Permission::latest()->paginate(10),
        ];
    }
}; ?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Permissions Management') }}
        </h2>
    </x-slot>

    @volt
    <div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    @if (session()->has('message'))
                        <div class="mb-4 bg-emerald-100 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg relative" role="alert">
                            <span class="block sm:inline font-medium">{{ session('message') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Permissions</h3>
                        <button wire:click="openModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                            Add New Permission
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">ID</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Created At</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @forelse($permissions as $permission)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $permission->id }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-200">
                                            {{ $permission->name }}
                                            @if(!($permission->is_active ?? true))
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $permission->created_at->format('M d, Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-right space-x-3">
                                            <button wire:click="openModal({{ $permission->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition-colors">Edit</button>
                                            <button wire:click="delete({{ $permission->id }})" wire:confirm="Are you sure you want to delete this permission?" class="text-rose-600 hover:text-rose-900 dark:text-rose-400 dark:hover:text-rose-300 font-medium transition-colors">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                <p>No permissions found. Create one to get started.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div x-data="{ open: @entangle('isModalOpen') }" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
            
            <!-- Modal Content -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden border border-slate-200 dark:border-slate-700">
                
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $permissionId ? 'Edit Permission' : 'Create Permission' }}</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors rounded-full p-1 hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <form wire:submit="save">
                    <div class="p-6">
                        <div>
                            <x-input-label for="name" :value="__('Permission Name')" class="text-slate-700 dark:text-slate-300" />
                            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus placeholder="e.g. create_users" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Names should be lowercase and snake_case.</p>
                        </div>
                        <div class="mt-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Active Permission</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-end gap-3">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                            <span wire:loading wire:target="save" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                            {{ $permissionId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-app-layout>
