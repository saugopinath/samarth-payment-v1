<?php

use function Laravel\Folio\{name, middleware};
use App\Models\OfficeMaster;
use App\Models\Codemaster;
use App\Models\RoleOfficeTypeMapping;
use App\Models\Role;
use App\Models\State;
use App\Models\District;
use App\Models\Subdivision;
use App\Models\Block;
use App\Models\Municipality;
use App\Models\Panchayat;
use App\Models\Ward;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithPagination;

name('management.offices');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $officeId = null;
    public string $name = '';
    public string $address = '';
    public string $zip = '';
    public $office_type_id = '';
    public $parent_id = '';
    public $state_id = '';
    public $district_id = '';
    
    // Geographical
    public string $location_type = ''; // 'rural' or 'urban'
    public $subdivision_id = '';
    public $block_id = '';
    public $municipality_id = '';
    public $panchayat_id = '';
    public $ward_id = '';
    
    public bool $is_active = true;
    public $max_operator = '';
    public $max_verifier = '';
    public $max_enquiry_officer = '';

    public bool $isModalOpen = false;

    // Lists
    public $districts = [];
    public $subdivisions = [];
    public $blocks = [];
    public $municipalities = [];
    public $panchayats = [];
    public $wards = [];

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'zip' => 'nullable|string|max:20',
            'office_type_id' => 'required|exists:codemasters,code',
            'parent_id' => 'nullable|exists:office_masters,id',
            'is_active' => 'boolean',
            'max_operator' => 'nullable|integer|min:0',
            'max_verifier' => 'nullable|integer|min:0',
            'max_enquiry_officer' => 'nullable|integer|min:0',
        ];

        if ($this->office_type_id) {
            $rules['state_id'] = 'required|exists:states,id';

            if ($this->office_type_id != 151) {
                $rules['district_id'] = 'required|exists:districts,id';
            }

            if (in_array($this->office_type_id, [153, 156])) {
                $rules['block_id'] = 'required|exists:blocks,id';
            }

            if ($this->office_type_id == 156) {
                $rules['panchayat_id'] = 'required|exists:panchayats,id';
            }

            if (in_array($this->office_type_id, [154, 155, 159])) {
                $rules['subdivision_id'] = 'required|exists:subdivisions,id';
            }

            if (in_array($this->office_type_id, [155, 159])) {
                $rules['municipality_id'] = 'required|exists:municipalities,id';
            }

            if ($this->office_type_id == 159) {
                $rules['ward_id'] = 'required|exists:wards,id';
            }
        }

        return $rules;
    }

    public function updatedStateId($stateId)
    {
        $this->districts = $stateId ? District::where('state_id', $stateId)->where('is_active', true)->orderBy('name')->get() : [];
        $this->reset(['district_id', 'location_type', 'subdivision_id', 'block_id', 'municipality_id', 'panchayat_id', 'ward_id']);
        $this->resetListsFromDistrict();
    }

    public function updatedDistrictId($districtId)
    {
        $this->reset(['subdivision_id', 'block_id', 'municipality_id', 'panchayat_id', 'ward_id']);
        $this->resetListsFromDistrict();
        
        if ($districtId) {
            if (in_array($this->office_type_id, [153, 156])) {
                $this->blocks = Block::where('district_id', $districtId)->where('is_active', true)->orderBy('name')->get();
            } elseif (in_array($this->office_type_id, [154, 155, 159])) {
                $this->subdivisions = Subdivision::where('district_id', $districtId)->where('is_active', true)->orderBy('name')->get();
            }
        }
    }

    public function updatedOfficeTypeId($typeId)
    {
        $this->reset(['subdivision_id', 'block_id', 'municipality_id', 'panchayat_id', 'ward_id', 'location_type']);
        $this->resetListsFromDistrict();
        
        if (in_array($typeId, [153, 156])) {
            $this->location_type = 'rural';
            if ($this->district_id) {
                $this->blocks = Block::where('district_id', $this->district_id)->where('is_active', true)->orderBy('name')->get();
            }
        } elseif (in_array($typeId, [154, 155, 159])) {
            $this->location_type = 'urban';
            if ($this->district_id) {
                $this->subdivisions = Subdivision::where('district_id', $this->district_id)->where('is_active', true)->orderBy('name')->get();
            }
        }
    }

    public function updatedLocationType($type)
    {
        // Now handled automatically via office_type_id, but kept for compatibility
    }

    public function updatedBlockId($blockId)
    {
        $this->reset(['panchayat_id']);
        $this->panchayats = $blockId ? Panchayat::where('block_id', $blockId)->where('is_active', true)->orderBy('name')->get() : [];
    }

    public function updatedSubdivisionId($subdivId)
    {
        $this->reset(['municipality_id', 'ward_id']);
        $this->municipalities = $subdivId ? Municipality::where('subdivision_id', $subdivId)->where('is_active', true)->orderBy('name')->get() : [];
        $this->wards = [];
    }

    public function updatedMunicipalityId($munId)
    {
        $this->reset(['ward_id']);
        $this->wards = $munId ? Ward::where('municipality_id', $munId)->where('is_active', true)->orderBy('ward_number')->get() : [];
    }

    protected function resetListsFromDistrict() {
        $this->blocks = [];
        $this->subdivisions = [];
        $this->municipalities = [];
        $this->panchayats = [];
        $this->wards = [];
    }

    public function loadDependencies() {
        if ($this->state_id) {
            $this->districts = District::where('state_id', $this->state_id)->where('is_active', true)->orderBy('name')->get();
        }
        if (in_array($this->office_type_id, [153, 156])) {
            if ($this->district_id) $this->blocks = Block::where('district_id', $this->district_id)->where('is_active', true)->orderBy('name')->get();
            if ($this->block_id) $this->panchayats = Panchayat::where('block_id', $this->block_id)->where('is_active', true)->orderBy('name')->get();
        } elseif (in_array($this->office_type_id, [154, 155, 159])) {
            if ($this->district_id) $this->subdivisions = Subdivision::where('district_id', $this->district_id)->where('is_active', true)->orderBy('name')->get();
            if ($this->subdivision_id) $this->municipalities = Municipality::where('subdivision_id', $this->subdivision_id)->where('is_active', true)->orderBy('name')->get();
            if ($this->municipality_id) $this->wards = Ward::where('municipality_id', $this->municipality_id)->where('is_active', true)->orderBy('ward_number')->get();
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset([
            'officeId', 'name', 'address', 'zip', 'office_type_id', 'parent_id', 'state_id', 'district_id',
            'location_type', 'subdivision_id', 'block_id', 'municipality_id', 'panchayat_id', 'ward_id',
            'is_active', 'max_operator', 'max_verifier', 'max_enquiry_officer'
        ]);
        $this->resetListsFromDistrict();
        $this->districts = [];

        if ($id) {
            $office = OfficeMaster::findOrFail($id);
            $this->officeId = $office->id;
            $this->name = $office->name;
            $this->address = $office->address ?? '';
            $this->zip = $office->zip ?? '';
            $this->office_type_id = $office->office_type_id;
            $this->parent_id = $office->parent_id;
            $this->state_id = $office->state_id;
            $this->district_id = $office->district_id;
            
            if (in_array($office->office_type_id, [153, 156])) {
                $this->location_type = 'rural';
            } elseif (in_array($office->office_type_id, [154, 155, 159])) {
                $this->location_type = 'urban';
            }
            
            $this->subdivision_id = $office->subdivision_id;
            $this->block_id = $office->block_id;
            $this->municipality_id = $office->municipalitiy_id; // Using DB column name for variable binding might be tricky, wait, the DB column is municipalitiy_id! I'll bind to $this->municipality_id but save to municipalitiy_id.
            $this->panchayat_id = $office->panchayat_id;
            $this->ward_id = $office->ward_id;
            
            $this->is_active = $office->is_active;
            $this->max_operator = $office->max_operator;
            $this->max_verifier = $office->max_verifier;
            $this->max_enquiry_officer = $office->max_enquiry_officer;

            $this->loadDependencies();
        } else {
            $this->is_active = true;
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

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'zip' => empty($this->zip) ? null : $this->zip,
            'office_type_id' => $this->office_type_id,
            'parent_id' => empty($this->parent_id) ? null : $this->parent_id,
            'state_id' => empty($this->state_id) ? null : $this->state_id,
            'district_id' => empty($this->district_id) ? null : $this->district_id,
            'subdivision_id' => empty($this->subdivision_id) ? null : $this->subdivision_id,
            'block_id' => empty($this->block_id) ? null : $this->block_id,
            'municipalitiy_id' => empty($this->municipality_id) ? null : $this->municipality_id,
            'panchayat_id' => empty($this->panchayat_id) ? null : $this->panchayat_id,
            'ward_id' => empty($this->ward_id) ? null : $this->ward_id,
            'is_active' => $this->is_active,
            'max_operator' => empty($this->max_operator) ? null : $this->max_operator,
            'max_verifier' => empty($this->max_verifier) ? null : $this->max_verifier,
            'max_enquiry_officer' => empty($this->max_enquiry_officer) ? null : $this->max_enquiry_officer,
        ];

        // Clear irrelevant fields based on office_type_id
        if (in_array($this->office_type_id, [153, 156])) {
            $data['subdivision_id'] = null;
            $data['municipalitiy_id'] = null;
            $data['ward_id'] = null;
        } elseif (in_array($this->office_type_id, [154, 155, 159])) {
            $data['block_id'] = null;
            $data['panchayat_id'] = null;
        } else {
            // State or District type, clear all downstream
            $data['subdivision_id'] = null;
            $data['block_id'] = null;
            $data['municipalitiy_id'] = null;
            $data['panchayat_id'] = null;
            $data['ward_id'] = null;
        }

        if ($this->office_type_id == 151) {
            $data['district_id'] = null;
        }

        if ($this->officeId) {
            $office = OfficeMaster::findOrFail($this->officeId);
            $office->update($data);
            session()->flash('message', 'Office updated successfully.');
        } else {
            OfficeMaster::create($data);
            session()->flash('message', 'Office created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        OfficeMaster::findOrFail($id)->delete();
        session()->flash('message', 'Office deleted successfully.');
    }

    public function with(): array
    {
        $parentOfficesQuery = OfficeMaster::with('officeType')->where('is_active', true)
            ->when($this->officeId, fn($q) => $q->where('id', '!=', $this->officeId));

        if ($this->office_type_id) {
            $mappedRoleIds = RoleOfficeTypeMapping::where('office_type_id', $this->office_type_id)->pluck('role_id');
            if ($mappedRoleIds->isNotEmpty()) {
                $minRank = Role::whereIn('id', $mappedRoleIds)->min('rank');
                if ($minRank) {
                    $parentRoleIds = Role::where('rank', '<', $minRank)->pluck('id');
                    $parentOfficeTypeIds = RoleOfficeTypeMapping::whereIn('role_id', $parentRoleIds)->pluck('office_type_id');
                    
                    if ($parentOfficeTypeIds->isNotEmpty()) {
                        $parentOfficesQuery->whereIn('office_type_id', $parentOfficeTypeIds);
                    }
                }
            }
        }

        return [
            'offices' => OfficeMaster::with(['officeType', 'state', 'district', 'subdivision', 'block', 'municipality', 'gp', 'ward'])
                ->orderBy('name')
                ->paginate(15),
            'parentOffices' => $parentOfficesQuery->orderBy('name')->get(),
            'states' => State::where('is_active', true)->orderBy('name')->get(),
            'officeTypes' => Codemaster::where('parent_short_code', 'office_type')->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}; ?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Office Management') }}
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
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Offices</h3>
                        <button wire:click="openModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                            Add New Office
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Type</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Location</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Status</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offices as $office)
                                    <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/25 transition-colors">
                                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100 font-medium">
                                            {{ $office->name }}
                                            <div class="text-xs text-slate-500 font-normal mt-0.5 truncate max-w-xs" title="{{ $office->address }}">{{ $office->address }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                            {{ $office->officeType->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                            @if($office->state)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                                    {{ $office->state->name }}
                                                </span>
                                            @endif
                                            @if($office->district)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                                    {{ $office->district->name }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($office->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right space-x-2">
                                            <button wire:click="openModal({{ $office->id }})" class="text-amber-500 hover:text-amber-700 font-medium transition-colors">
                                                Edit
                                            </button>
                                            <button wire:click="delete({{ $office->id }})" wire:confirm="Are you sure you want to delete this office?" class="text-rose-500 hover:text-rose-700 font-medium transition-colors">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                            No offices found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $offices->links() }}
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

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit="save">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100" id="modal-title">
                                {{ $officeId ? 'Edit Office' : 'Create Office' }}
                            </h3>
                        </div>
                        
                        <div class="px-6 py-4 space-y-6 max-h-[70vh] overflow-y-auto">
                            <!-- Basic Details -->
                            <div>
                                <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Basic Details</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <x-input-label for="name" :value="__('Office Name')" />
                                        <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-input-label for="address" :value="__('Address')" />
                                        <textarea id="address" wire:model="address" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" rows="2" required></textarea>
                                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="office_type_id" :value="__('Office Type')" />
                                        <select id="office_type_id" wire:model.live="office_type_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                            <option value="">Select Type</option>
                                            @foreach($officeTypes as $type)
                                                <option value="{{ $type->code }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('office_type_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="parent_id" :value="__('Parent Office')" />
                                        <select id="parent_id" wire:model="parent_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                            <option value="">None (Optional)</option>
                                            @foreach($parentOffices as $parent)
                                                <option value="{{ $parent->id }}">{{ $parent->name }} {{ $parent->officeType ? '('.$parent->officeType->name.')' : '' }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="zip" :value="__('ZIP Code')" />
                                        <x-text-input id="zip" type="text" class="mt-1 block w-full" wire:model="zip" />
                                        <x-input-error :messages="$errors->get('zip')" class="mt-2" />
                                    </div>
                                    
                                    <div>
                                        <label class="inline-flex items-center mt-4">
                                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50 dark:bg-slate-800 dark:border-slate-600">
                                            <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">{{ __('Is Active?') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-slate-200 dark:border-slate-700">

                            @if($office_type_id)
                            <div>
                                <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Location Context</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="state_id" :value="__('State')" />
                                        <select id="state_id" wire:model.live="state_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                            <option value="">Select State</option>
                                            @foreach($states as $state)
                                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('state_id')" class="mt-2" />
                                    </div>

                                    @if($office_type_id != 151)
                                    <div>
                                        <x-input-label for="district_id" :value="__('District')" />
                                        <select id="district_id" wire:model.live="district_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" {{ empty($districts) ? 'disabled' : '' }}>
                                            <option value="">Select District</option>
                                            @foreach($districts as $district)
                                                <option value="{{ $district->id }}">{{ $district->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('district_id')" class="mt-2" />
                                    </div>
                                    @endif
                                </div>
                                
                                @if(in_array($office_type_id, [153, 156]))
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <div>
                                            <x-input-label for="block_id" :value="__('Block')" />
                                            <select id="block_id" wire:model.live="block_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" {{ empty($blocks) ? 'disabled' : '' }}>
                                                <option value="">Select Block</option>
                                                @foreach($blocks as $block)
                                                    <option value="{{ $block->id }}">{{ $block->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('block_id')" class="mt-2" />
                                        </div>
                                        @if($office_type_id == 156)
                                        <div>
                                            <x-input-label for="panchayat_id" :value="__('Panchayat')" />
                                            <select id="panchayat_id" wire:model="panchayat_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" {{ empty($panchayats) ? 'disabled' : '' }}>
                                                <option value="">Select Panchayat</option>
                                                @foreach($panchayats as $gp)
                                                    <option value="{{ $gp->id }}">{{ $gp->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('panchayat_id')" class="mt-2" />
                                        </div>
                                        @endif
                                    </div>
                                @elseif(in_array($office_type_id, [154, 155, 159]))
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <div>
                                            <x-input-label for="subdivision_id" :value="__('Subdivision')" />
                                            <select id="subdivision_id" wire:model.live="subdivision_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" {{ empty($subdivisions) ? 'disabled' : '' }}>
                                                <option value="">Select Subdivision</option>
                                                @foreach($subdivisions as $sub)
                                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('subdivision_id')" class="mt-2" />
                                        </div>
                                        @if(in_array($office_type_id, [155, 159]))
                                        <div>
                                            <x-input-label for="municipality_id" :value="__('Municipality')" />
                                            <select id="municipality_id" wire:model.live="municipality_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" {{ empty($municipalities) ? 'disabled' : '' }}>
                                                <option value="">Select Municipality</option>
                                                @foreach($municipalities as $mun)
                                                    <option value="{{ $mun->id }}">{{ $mun->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('municipality_id')" class="mt-2" />
                                        </div>
                                        @endif
                                        @if($office_type_id == 159)
                                        <div>
                                            <x-input-label for="ward_id" :value="__('Ward')" />
                                            <select id="ward_id" wire:model="ward_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" {{ empty($wards) ? 'disabled' : '' }}>
                                                <option value="">Select Ward</option>
                                                @foreach($wards as $ward)
                                                    <option value="{{ $ward->id }}">{{ $ward->ward_number ?? $ward->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('ward_id')" class="mt-2" />
                                        </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @endif

                            <hr class="border-slate-200 dark:border-slate-700">

                            <!-- Capacities -->
                            <div>
                                <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Capacities</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label for="max_operator" :value="__('Max Operators')" />
                                        <x-text-input id="max_operator" type="number" min="0" class="mt-1 block w-full" wire:model="max_operator" />
                                    </div>
                                    <div>
                                        <x-input-label for="max_verifier" :value="__('Max Verifiers')" />
                                        <x-text-input id="max_verifier" type="number" min="0" class="mt-1 block w-full" wire:model="max_verifier" />
                                    </div>
                                    <div>
                                        <x-input-label for="max_enquiry_officer" :value="__('Max Enquiry Officers')" />
                                        <x-text-input id="max_enquiry_officer" type="number" min="0" class="mt-1 block w-full" wire:model="max_enquiry_officer" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                            <button type="button" wire:click="closeModal" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-amber-500 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                Save Office
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
