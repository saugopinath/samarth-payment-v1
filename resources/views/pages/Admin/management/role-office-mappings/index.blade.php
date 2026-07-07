<?php

use function Laravel\Folio\{name, middleware};
use App\Models\Role;
use App\Models\Codemaster;
use App\Models\RoleOfficeTypeMapping;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithPagination;

name('management.role-office-mappings');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $mappingId = null;
    public $role_id = '';
    public $office_type_id = '';
    public bool $isModalOpen = false;

    public function rules()
    {
        return [
            'role_id' => 'required|exists:roles,id',
            'office_type_id' => [
                'required',
                'exists:codemasters,code',
                Rule::unique('role_office_type_mappings')->where(function ($query) {
                    return $query->where('role_id', $this->role_id)
                                 ->where('office_type_id', $this->office_type_id);
                })->ignore($this->mappingId),
            ],
        ];
    }

    public function messages()
    {
        return [
            'office_type_id.unique' => 'This Role is already mapped to this Office Type.',
        ];
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('role_id', 'office_type_id', 'mappingId');

        if ($id) {
            $mapping = RoleOfficeTypeMapping::findOrFail($id);
            $this->mappingId = $mapping->id;
            $this->role_id = $mapping->role_id;
            $this->office_type_id = $mapping->office_type_id;
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
        $this->reset('role_id', 'office_type_id', 'mappingId');
    }

    public function save()
    {
        $this->validate();

        if ($this->mappingId) {
            $mapping = RoleOfficeTypeMapping::findOrFail($this->mappingId);
            $mapping->update([
                'role_id' => $this->role_id,
                'office_type_id' => $this->office_type_id
            ]);
            session()->flash('message', 'Mapping updated successfully.');
        } else {
            RoleOfficeTypeMapping::create([
                'role_id' => $this->role_id,
                'office_type_id' => $this->office_type_id
            ]);
            session()->flash('message', 'Mapping created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        RoleOfficeTypeMapping::findOrFail($id)->delete();
        session()->flash('message', 'Mapping deleted successfully.');
    }

    public function with(): array
    {
        return [
            'mappings' => RoleOfficeTypeMapping::with(['role', 'officeType'])->paginate(10),
            'roles' => Role::orderBy('name')->get(),
            'officeTypes' => Codemaster::where('parent_short_code', 'office_type')->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}; ?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Role & Office Type Mappings') }}
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
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Mappings</h3>
                        <button wire:click="openModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                            Add New Mapping
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Role</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Office Type</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mappings as $mapping)
                                    <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/25 transition-colors">
                                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100 font-medium">
                                            {{ $mapping->role->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                            {{ $mapping->officeType->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right space-x-2">
                                            <button wire:click="openModal({{ $mapping->id }})" class="text-amber-500 hover:text-amber-700 font-medium transition-colors">
                                                Edit
                                            </button>
                                            <button wire:click="delete({{ $mapping->id }})" wire:confirm="Are you sure you want to delete this mapping?" class="text-rose-500 hover:text-rose-700 font-medium transition-colors">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                            No mappings found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $mappings->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit="save">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100" id="modal-title">
                                {{ $mappingId ? 'Edit Mapping' : 'Create Mapping' }}
                            </h3>
                        </div>
                        
                        <div class="px-6 py-4 space-y-4">
                            <div>
                                <x-input-label for="role_id" :value="__('Role')" />
                                <select id="role_id" wire:model="role_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="office_type_id" :value="__('Office Type')" />
                                <select id="office_type_id" wire:model="office_type_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                    <option value="">Select Office Type</option>
                                    @foreach($officeTypes as $officeType)
                                        <option value="{{ $officeType->code }}">{{ $officeType->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('office_type_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                            <button type="button" wire:click="closeModal" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-amber-500 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                Save Mapping
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endvolt
</x-app-layout>
