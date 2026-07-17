<?php use function Laravel\Folio\{name, middleware}; name('management.document-types'); middleware(['auth', 'verified']); ?>
<?php

use App\Models\DocumentTypeMaster;
use App\Models\Codemaster;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $docTypeId = null;
    public $document_type_code = '';
    public $document_mime_type = '';
    public $document_extension = '';
    public $max_size = '';

    public $search = '';
    
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $docTypeToDelete = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createDocType()
    {
        $this->resetValidation();
        $this->reset(['docTypeId', 'document_type_code', 'document_mime_type', 'document_extension', 'max_size']);
        $this->isModalOpen = true;
    }

    public function editDocType($id)
    {
        $this->resetValidation();
        $docType = DocumentTypeMaster::findOrFail($id);
        $this->docTypeId = $docType->id;
        $this->document_type_code = $docType->document_type_code;
        $this->document_mime_type = is_array($docType->document_mime_type) ? implode(', ', $docType->document_mime_type) : '';
        $this->document_extension = is_array($docType->document_extension) ? implode(', ', $docType->document_extension) : '';
        $this->max_size = $docType->max_size;
        
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['docTypeId', 'document_type_code', 'document_mime_type', 'document_extension', 'max_size']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'document_type_code' => 'required|string',
            'document_mime_type' => 'nullable|string',
            'document_extension' => 'nullable|string',
            'max_size' => 'nullable|integer|min:1',
        ]);

        $mimeArray = $this->document_mime_type ? array_filter(array_map('trim', explode(',', $this->document_mime_type))) : null;
        $extArray = $this->document_extension ? array_filter(array_map('trim', explode(',', $this->document_extension))) : null;

        $data = [
            'document_type_code' => $this->document_type_code,
            'document_mime_type' => empty($mimeArray) ? null : $mimeArray,
            'document_extension' => empty($extArray) ? null : $extArray,
            'max_size' => $this->max_size ?: null,
        ];

        if ($this->docTypeId) {
            $docType = DocumentTypeMaster::findOrFail($this->docTypeId);
            $docType->update($data);
            session()->flash('success', 'Document Type updated successfully!');
        } else {
            DocumentTypeMaster::create($data);
            session()->flash('success', 'Document Type created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->docTypeToDelete = $id;
        $this->isDeleteModalOpen = true;
    }

    public function deleteDocType()
    {
        if ($this->docTypeToDelete) {
            $docType = DocumentTypeMaster::find($this->docTypeToDelete);
            if ($docType) {
                $docType->delete();
                session()->flash('success', 'Document Type deleted successfully!');
            }
        }
        $this->isDeleteModalOpen = false;
        $this->docTypeToDelete = null;
    }

    public function with()
    {
        $query = DocumentTypeMaster::with('codemaster');
        if ($this->search) {
            $query->whereHas('codemaster', function($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('code', 'ilike', '%' . $this->search . '%');
            });
        }
        
        return [
            'documentTypes' => $query->orderBy('id', 'desc')->paginate(10),
            'codemasters' => Codemaster::where('parent_short_code', 'ENCDETAILS')->get()
        ];
    }
};
?>
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                {{ __('Document Type Master') }}
            </h2>
        </div>
    </x-slot>

    @volt
    <div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex justify-end">
                <button wire:click="createDocType" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Add Document Type
                </button>
            </div>
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    
                    @if (session()->has('success'))
                        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    <div class="flex justify-end mb-4">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or code..." class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block w-1/3">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MIME Types</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extensions</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Size</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($documentTypes as $type)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $type->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $type->document_type_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($type->codemaster)->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @if($type->document_mime_type)
                                                {{ implode(', ', $type->document_mime_type) }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @if($type->document_extension)
                                                {{ implode(', ', $type->document_extension) }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $type->max_size ? $type->max_size . ' KB' : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="editDocType({{ $type->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            <button wire:click="confirmDelete({{ $type->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No document types found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $documentTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($isModalOpen)
    <div class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form wire:submit.prevent="save">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $docTypeId ? 'Edit Document Type' : 'Add Document Type' }}
                        </h3>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Document Type (From ENCDETAILS)</label>
                        <select wire:model="document_type_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select Document Type</option>
                            @foreach($codemasters as $master)
                                <option value="{{ $master->code }}">{{ $master->name }} ({{ $master->code }})</option>
                            @endforeach
                        </select>
                        @error('document_type_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">MIME Types</label>
                        <input type="text" wire:model="document_mime_type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g. application/pdf, image/jpeg">
                        <p class="text-xs text-gray-500 mt-1">Separate multiple MIME types with a comma.</p>
                        @error('document_mime_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Extensions</label>
                        <input type="text" wire:model="document_extension" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g. pdf, jpg, png">
                        <p class="text-xs text-gray-500 mt-1">Separate multiple extensions with a comma.</p>
                        @error('document_extension') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Max Size (in KB)</label>
                        <input type="number" wire:model="max_size" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g. 5000">
                        @error('max_size') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Delete Modal -->
    @if($isDeleteModalOpen)
    <div class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Document Type</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this document type? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="deleteDocType" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Delete
                </button>
                <button type="button" wire:click="$set('isDeleteModalOpen', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
    @endvolt
</x-app-layout>
