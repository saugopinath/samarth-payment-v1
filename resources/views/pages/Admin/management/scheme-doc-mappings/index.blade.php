<?php

use function Laravel\Folio\{name, middleware};
use App\Models\SchemeAttachedDocMappings;
use App\Models\Scheme;
use App\Models\Codemaster;
use Livewire\Volt\Component;
use Livewire\WithPagination;

name('management.scheme-doc-mappings');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $mappingId = null;
    public $scheme_id = '';
    public $doc_type_id = '';
    public $tab_code = '';
    public $is_required = false;
    public $max_file_size = '';
    public $extension_type = '';
    public $field_position = 1;
    public $is_active = true;

    public bool $isModalOpen = false;

    public function rules()
    {
        return [
            'scheme_id' => 'required|exists:schemes,id',
            'doc_type_id' => 'required|exists:codemasters,id',
            'tab_code' => 'nullable|string|max:255',
            'is_required' => 'boolean',
            'max_file_size' => 'nullable|numeric|min:1',
            'extension_type' => 'nullable|string|max:255',
            'field_position' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['mappingId', 'scheme_id', 'doc_type_id', 'tab_code', 'is_required', 'max_file_size', 'extension_type', 'field_position', 'is_active']);

        if ($id) {
            $mapping = SchemeAttachedDocMappings::findOrFail($id);
            $this->mappingId = $mapping->id;
            $this->scheme_id = $mapping->scheme_id;
            $this->doc_type_id = $mapping->doc_type_id;
            $this->tab_code = $mapping->tab_code;
            $this->is_required = $mapping->is_required;
            $this->max_file_size = $mapping->max_file_size;
            $this->extension_type = $mapping->extension_type;
            $this->field_position = $mapping->field_position;
            $this->is_active = $mapping->is_active;
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        if ($this->mappingId) {
            $mapping = SchemeAttachedDocMappings::findOrFail($this->mappingId);
            $mapping->update([
                'scheme_id' => $this->scheme_id,
                'doc_type_id' => $this->doc_type_id,
                'tab_code' => $this->tab_code,
                'is_required' => $this->is_required,
                'max_file_size' => $this->max_file_size,
                'extension_type' => $this->extension_type,
                'field_position' => $this->field_position,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Document mapping updated successfully.');
        } else {
            SchemeAttachedDocMappings::create([
                'scheme_id' => $this->scheme_id,
                'doc_type_id' => $this->doc_type_id,
                'tab_code' => $this->tab_code,
                'is_required' => $this->is_required,
                'max_file_size' => $this->max_file_size,
                'extension_type' => $this->extension_type,
                'field_position' => $this->field_position,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Document mapping created successfully.');
        }

        $this->closeModal();
    }

    public function with(): array
    {
        return [
            'mappings' => SchemeAttachedDocMappings::with(['docType'])->latest()->paginate(10),
            'schemes' => Scheme::where('is_active', true)->get(),
            'docTypes' => Codemaster::where('parent_id', 16)->where('is_active', true)->get(),
        ];
    }
}; ?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Scheme Document Mappings') }}
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
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Attached Document Mappings</h3>
                        <button wire:click="openModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                            Add New Mapping
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Scheme ID</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Document Type</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Configuration</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Status</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @forelse($mappings as $mapping)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
                                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-200">
                                            {{ $mapping->scheme_id }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-900 dark:text-slate-200">{{ $mapping->docType->name ?? 'Unknown' }}</div>
                                            <div class="text-xs text-slate-500">Tab Code: {{ $mapping->tab_code ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            <div><span class="font-medium text-slate-500">Required:</span> {{ $mapping->is_required ? 'Yes' : 'No' }}</div>
                                            <div><span class="font-medium text-slate-500">Max Size:</span> {{ $mapping->max_file_size }} KB</div>
                                            <div><span class="font-medium text-slate-500">Exts:</span> {{ $mapping->extension_type }}</div>
                                            <div><span class="font-medium text-slate-500">Position:</span> {{ $mapping->field_position }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($mapping->is_active)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Active</span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            <button wire:click="openModal({{ $mapping->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition-colors">Edit</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <p>No document mappings found.</p>
                                            </div>
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
        <div x-data="{ open: @entangle('isModalOpen') }" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
            
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]">
                
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $mappingId ? 'Edit Mapping' : 'Create Mapping' }}</h3>
                    <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors rounded-full p-1 hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-6 overflow-y-auto space-y-4">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="scheme_id" :value="__('Scheme')" class="text-slate-700 dark:text-slate-300" />
                                <select wire:model="scheme_id" id="scheme_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                    <option value="">Select Scheme</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->id }}">{{ $scheme->name }} (ID: {{ $scheme->id }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('scheme_id')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="doc_type_id" :value="__('Document Type')" class="text-slate-700 dark:text-slate-300" />
                                <select wire:model="doc_type_id" id="doc_type_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                    <option value="">Select Document Type</option>
                                    @foreach($docTypes as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('doc_type_id')" class="mt-1" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="tab_code" :value="__('Tab Code')" class="text-slate-700 dark:text-slate-300" />
                                <x-text-input wire:model="tab_code" id="tab_code" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('tab_code')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="field_position" :value="__('Field Position')" class="text-slate-700 dark:text-slate-300" />
                                <x-text-input wire:model="field_position" id="field_position" class="block mt-1 w-full" type="number" min="1" />
                                <x-input-error :messages="$errors->get('field_position')" class="mt-1" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="max_file_size" :value="__('Max File Size (KB)')" class="text-slate-700 dark:text-slate-300" />
                                <x-text-input wire:model="max_file_size" id="max_file_size" class="block mt-1 w-full" type="number" />
                                <x-input-error :messages="$errors->get('max_file_size')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="extension_type" :value="__('Extension Types (e.g. jpg,png,pdf)')" class="text-slate-700 dark:text-slate-300" />
                                <x-text-input wire:model="extension_type" id="extension_type" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('extension_type')" class="mt-1" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model="is_required" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Is Required?</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Active</span>
                                </label>
                            </div>
                        </div>

                    </div>
                    
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-end gap-3 mt-auto">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                            <span wire:loading wire:target="save" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                            {{ $mappingId ? 'Update Mapping' : 'Save Mapping' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-app-layout>
