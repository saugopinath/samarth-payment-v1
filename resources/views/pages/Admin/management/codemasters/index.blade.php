<?php use function Laravel\Folio\{name, middleware}; name('management.codemasters'); middleware(['auth', 'verified']); ?>
<?php

use App\Models\Codemaster;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $codemasterId = null;
    public $name = '';
    public $short_name = '';
    public $parent_id = null;
    public $is_active = 1;
    public $code = '';
    public $rank = null;
    public $parent_short_code = '';
    public $search = '';
    
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $codemasterToDelete = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedParentId($value)
    {
        if ($value) {
            $parent = Codemaster::find($value);
            if ($parent) {
                $this->parent_short_code = $parent->short_name;
                
                $children = Codemaster::where('parent_id', $value)->get();
                if ($children->count() > 0) {
                    $maxCode = $children->map(fn($c) => (int)$c->code)->max();
                    $this->code = (string) ($maxCode + 1);
                    
                    $maxRank = $children->max('rank');
                    $this->rank = $maxRank ? $maxRank + 1 : 1;
                } else {
                    $this->code = $parent->code ? $parent->code . '1' : '1';
                    $this->rank = 1;
                }
            }
        } else {
            $this->parent_short_code = '';
            
            $roots = Codemaster::whereNull('parent_id')->get();
            if ($roots->count() > 0) {
                $maxCode = $roots->map(fn($c) => (int)$c->code)->max();
                $this->code = (string) ($maxCode + 1);
                
                $maxRank = $roots->max('rank');
                $this->rank = $maxRank ? $maxRank + 1 : 1;
            } else {
                $this->code = '1';
                $this->rank = 1;
            }
        }
    }

    public function createCodemaster()
    {
        $this->resetValidation();
        $this->reset(['codemasterId', 'name', 'short_name', 'parent_id', 'code', 'rank', 'parent_short_code']);
        $this->is_active = 1;
        $this->isModalOpen = true;
        
        // Auto-fill root code and rank when opening form
        $this->updatedParentId(null);
    }

    public function editCodemaster($id)
    {
        $this->resetValidation();
        $codemaster = Codemaster::findOrFail($id);
        $this->codemasterId = $codemaster->id;
        $this->name = $codemaster->name;
        $this->short_name = $codemaster->short_name;
        $this->parent_id = $codemaster->parent_id;
        $this->is_active = $codemaster->is_active;
        $this->code = $codemaster->code;
        $this->rank = $codemaster->rank;
        $this->parent_short_code = $codemaster->parent_short_code;
        
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['codemasterId', 'name', 'short_name', 'parent_id', 'is_active', 'code', 'rank', 'parent_short_code']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'is_active' => 'required|boolean',
            'code' => 'nullable|string|max:255',
            'rank' => 'nullable|integer',
            'parent_short_code' => 'nullable|string|max:255',
        ]);

        $data = [
            'name' => $this->name,
            'short_name' => $this->short_name,
            'parent_id' => $this->parent_id ?: null,
            'is_active' => $this->is_active ? 1 : 0,
            'code' => $this->code,
            'rank' => $this->rank ?: null,
            'parent_short_code' => $this->parent_short_code,
        ];

        if ($this->codemasterId) {
            $codemaster = Codemaster::findOrFail($this->codemasterId);
            $codemaster->update($data);
            session()->flash('success', 'Codemaster updated successfully!');
        } else {
            Codemaster::create($data);
            session()->flash('success', 'Codemaster created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->codemasterToDelete = $id;
        $this->isDeleteModalOpen = true;
    }

    public function deleteCodemaster()
    {
        if ($this->codemasterToDelete) {
            $codemaster = Codemaster::find($this->codemasterToDelete);
            if ($codemaster) {
                $codemaster->delete();
                session()->flash('success', 'Codemaster deleted successfully!');
            }
        }
        $this->isDeleteModalOpen = false;
        $this->codemasterToDelete = null;
    }

    public function with(): array
    {
        $query = Codemaster::query();
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('short_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('parent_short_code', 'ilike', '%' . $this->search . '%');
            });
        }

        return [
            'codemasters' => $query->orderByRaw('COALESCE(parent_id, id) ASC, parent_id NULLS FIRST')->orderBy('name')->paginate(100),
            'parentOptions' => Codemaster::orderBy('name')->get(),
        ];
    }
}; ?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Codemasters') }}
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
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Manage Codemasters</h3>
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/></svg>
                                </div>
                                <input type="search" wire:model.live.debounce.300ms="search" class="block w-full sm:w-64 p-2 pl-10 text-sm text-slate-900 border border-slate-300 rounded-lg bg-slate-50 focus:ring-amber-500 focus:border-amber-500 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-amber-500 dark:focus:border-amber-500" placeholder="Search codemasters...">
                            </div>
                            <button wire:click="createCodemaster" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add Codemaster
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-700/50">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600 w-16">ID</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Parent Code</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Short Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Code</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">Status</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse($codemasters as $item)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/25 transition-colors">
                                        <td class="px-4 py-3 text-sm">{{ $item->id }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-amber-600 dark:text-amber-400">{{ $item->parent_short_code ?: 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium">{{ $item->name }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $item->short_name }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $item->code }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($item->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button wire:click="editCodemaster({{ $item->id }})" class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 font-medium mr-3">Edit</button>
                                            <button wire:click="confirmDelete({{ $item->id }})" class="text-sm text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 font-medium">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No codemasters found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $codemasters->links() }}
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
                             class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]">
                            
                            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $codemasterId ? 'Edit Codemaster' : 'Add Codemaster' }}</h3>
                                <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors rounded-full p-1 hover:bg-slate-200 dark:hover:bg-slate-700">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <form wire:submit="save" class="flex flex-col overflow-hidden">
                                <div class="p-6 overflow-y-auto">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="name" :value="__('Name')" />
                                            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required />
                                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="short_name" :value="__('Short Name')" />
                                            <x-text-input wire:model="short_name" id="short_name" class="block mt-1 w-full" type="text" required />
                                            <x-input-error :messages="$errors->get('short_name')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="code" :value="__('Code')" />
                                            <x-text-input wire:model="code" id="code" class="block mt-1 w-full" type="text" />
                                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="parent_short_code" :value="__('Parent Short Code')" />
                                            <x-text-input wire:model="parent_short_code" id="parent_short_code" class="block mt-1 w-full" type="text" />
                                            <x-input-error :messages="$errors->get('parent_short_code')" class="mt-2" />
                                        </div>

                                        <div>
                                            <x-input-label for="parent_id" :value="__('Parent')" />
                                            <select wire:model.live="parent_id" id="parent_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 dark:focus:border-amber-600 focus:ring-amber-500 dark:focus:ring-amber-600 rounded-md shadow-sm">
                                                <option value="">None</option>
                                                @foreach($parentOptions as $option)
                                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="rank" :value="__('Rank')" />
                                            <x-text-input wire:model="rank" id="rank" class="block mt-1 w-full" type="number" />
                                            <x-input-error :messages="$errors->get('rank')" class="mt-2" />
                                        </div>
                                        
                                        <div class="md:col-span-2 mt-2">
                                            <label for="is_active" class="inline-flex items-center">
                                                <input id="is_active" type="checkbox" wire:model="is_active" class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-amber-500 shadow-sm focus:ring-amber-500 dark:focus:ring-amber-600 dark:focus:ring-offset-slate-800" value="1">
                                                <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">{{ __('Is Active') }}</span>
                                            </label>
                                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
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
                            
                            <p class="text-slate-600 dark:text-slate-400 mb-6">Are you sure you want to delete this codemaster? This action cannot be undone.</p>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                    Cancel
                                </button>
                                <button type="button" wire:click="deleteCodemaster" class="px-4 py-2 text-sm font-medium bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                                    <span wire:loading wire:target="deleteCodemaster" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
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
