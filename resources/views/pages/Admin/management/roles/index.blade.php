<?php

use function Laravel\Folio\{name, middleware};
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Volt\Component;

name('management.roles');
middleware(['auth', 'verified']);

new class extends Component {
    public string $name = '';
    public int $rank = 0;
    public array $selectedPermissions = [];
    public $roleId = null;
    public bool $isModalOpen = false;
    public bool $is_active = true;

    public function rules()
    {
        $uniqueRule = 'unique:roles,name';
        if ($this->roleId) {
            $uniqueRule .= ',' . $this->roleId;
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'rank' => 'required|integer|min:0',
            'selectedPermissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('name', 'rank', 'selectedPermissions', 'roleId', 'is_active');

        if ($id) {
            $role = Role::with('permissions')->findOrFail($id);
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->rank = $role->rank ?? 0;
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
            $this->is_active = $role->is_active ?? true;
        } else {
            $this->rank = Role::max('rank') + 1;
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
        $this->reset('name', 'rank', 'selectedPermissions', 'roleId', 'is_active');
    }

    public function save()
    {
        $this->validate();

        if ($this->roleId) {
            $role = Role::findOrFail($this->roleId);
            $role->update([
                'name' => $this->name,
                'rank' => $this->rank,
                'is_active' => $this->is_active
            ]);
            $role->syncPermissions($this->selectedPermissions);
            session()->flash('message', 'Role updated successfully.');
        } else {
            $role = Role::create([
                'name' => $this->name,
                'rank' => $this->rank,
                'is_active' => $this->is_active
            ]);
            $role->syncPermissions($this->selectedPermissions);
            session()->flash('message', 'Role created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Role::findOrFail($id)->delete();
        session()->flash('message', 'Role deleted successfully.');
    }

    public function updateOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            Role::where('id', $id)->update(['rank' => $index + 1]);
        }
        session()->flash('message', 'Roles reordered successfully.');
    }

    public function with(): array
    {
        return [
            'roles' => Role::with('permissions')->orderBy('rank')->orderBy('id')->get(),
            'permissions' => Permission::all(),
        ];
    }
}; ?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Roles & Ranks Management') }}
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
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Roles</h3>
                        <button wire:click="openModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                            Add New Role
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Rank</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Role Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Permissions</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 text-right">Actions</th>
                                </tr>
                            </thead>
                        <tbody 
                            x-data="{
                                init() {
                                    if (typeof Sortable === 'undefined') {
                                        // Wait for script to load if necessary
                                        setTimeout(() => this.init(), 100);
                                        return;
                                    }
                                    new Sortable(this.$el, {
                                        handle: '.cursor-move',
                                        animation: 150,
                                        ghostClass: 'bg-slate-100',
                                        onEnd: (evt) => {
                                            let items = Array.from(this.$el.querySelectorAll('tr[data-id]')).map(row => row.dataset.id);
                                            $wire.updateOrder(items);
                                        }
                                    });
                                }
                            }"
                            class="divide-y divide-slate-100 dark:divide-slate-700/50"
                        >
                            @forelse($roles as $role)
                                <tr data-id="{{ $role->id }}" wire:key="role-{{ $role->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-200 w-16">
                                        <div class="cursor-move inline-flex items-center justify-center text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 transition-colors" title="Drag to reorder">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 12h16" /></svg>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-200">
                                        {{ $role->name }}
                                        @if(!($role->is_active ?? true))
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                        <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                            <div class="flex flex-wrap gap-1 max-w-md">
                                                @forelse($role->permissions as $permission)
                                                    <span class="inline-block px-2 py-1 text-[10px] font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 rounded border border-amber-200 dark:border-amber-800">{{ $permission->name }}</span>
                                                @empty
                                                    <span class="text-slate-400 italic text-xs">No permissions assigned</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right space-x-3">
                                            <button wire:click="openModal({{ $role->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition-colors">Edit</button>
                                            <button wire:click="delete({{ $role->id }})" wire:confirm="Are you sure you want to delete this role?" class="text-rose-600 hover:text-rose-900 dark:text-rose-400 dark:hover:text-rose-300 font-medium transition-colors">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                <p>No roles found. Create one to get started.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                 class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]">
                
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $roleId ? 'Edit Role' : 'Create Role' }}</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors rounded-full p-1 hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-6 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="name" :value="__('Role Name')" class="text-slate-700 dark:text-slate-300" />
                                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus placeholder="e.g. administrator" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="rank" :value="__('Role Rank')" class="text-slate-700 dark:text-slate-300" />
                                <x-text-input wire:model="rank" id="rank" class="block mt-1 w-full" type="number" min="0" name="rank" required />
                                <x-input-error :messages="$errors->get('rank')" class="mt-2" />
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Lower numbers imply higher hierarchy (e.g. 1 = Super Admin).</p>
                            </div>
                            <div class="md:col-span-2 flex items-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Active Role</span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Assign Permissions</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @forelse($permissions as $permission)
                                    <label class="flex items-start space-x-3 p-3 border border-slate-100 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="mt-1 rounded border-slate-300 dark:border-slate-600 text-amber-500 focus:ring-amber-500 dark:bg-slate-800">
                                        <span class="text-sm text-slate-700 dark:text-slate-300 font-medium break-all leading-tight">{{ $permission->name }}</span>
                                    </label>
                                @empty
                                    <div class="col-span-full text-center text-sm text-slate-500 dark:text-slate-400 py-4">
                                        No permissions available. Please create permissions first.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-end gap-3 mt-auto">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                            <span wire:loading wire:target="save" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                            {{ $roleId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/Sortable.min.js') }}"></script>
    @endvolt
</x-app-layout>
