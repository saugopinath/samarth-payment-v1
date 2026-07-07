<?php

use function Laravel\Folio\{name, middleware};
use App\Models\User;
use App\Models\Scheme;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\OfficeMaster;
use App\Models\UserRoleSchemeOfficeMapping;
use App\Models\RoleOfficeTypeMapping;
use App\Models\Codemaster;
use App\Models\State;
use App\Models\District;
use App\Models\Subdivision;
use App\Models\Block;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

name('management.users');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $userId = null;
    public string $name = '';
    public string $email = '';
    public string $mobile_no = '';
    public string $designation = '';
    public bool $is_active = true;
    public bool $bypass_otp = false;
    public bool $allow_multi_session = false;

    public $filterRoleId = '';
    public $filterOfficeTypeId = '';
    public $filterStateId = '';
    public $filterDistrictId = '';
    public $filterSubdivisionId = '';
    public $filterBlockId = '';
    public $filterOfficeId = '';

    public array $mappings = [];

    public bool $isModalOpen = false;

    public function updatedFilterRoleId() { $this->resetPage(); }
    public function updatedFilterOfficeTypeId() { $this->resetPage(); }
    public function updatedFilterStateId() { $this->resetPage(); }
    public function updatedFilterDistrictId() { $this->resetPage(); }
    public function updatedFilterSubdivisionId() { $this->resetPage(); }
    public function updatedFilterBlockId() { $this->resetPage(); }
    public function updatedFilterOfficeId() { $this->resetPage(); }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'mobile_no' => ['required', 'string', 'max:15', Rule::unique('users')->ignore($this->userId)],
            'designation' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'bypass_otp' => 'boolean',
            'allow_multi_session' => 'boolean',
            'mappings' => 'array',
            'mappings.*.scheme_id' => 'required|exists:schemes,id',
            'mappings.*.role_id' => 'required|exists:roles,id',
            'mappings.*.office_id' => 'required|exists:office_masters,id',
            'mappings.*.permissions' => 'array',
        ];
    }

    public function addMapping()
    {
        $this->mappings[] = [
            'id' => null,
            'scheme_id' => '',
            'role_id' => '',
            'office_id' => '',
            'permissions' => []
        ];
    }

    public function getOfficesForRole($roleId)
    {
        if (!$roleId) return [];
        $officeTypeIds = RoleOfficeTypeMapping::where('role_id', $roleId)->pluck('office_type_id');
        return OfficeMaster::whereIn('office_type_id', $officeTypeIds)->where('is_active', true)->get();
    }

    public function removeMapping($index)
    {
        unset($this->mappings[$index]);
        $this->mappings = array_values($this->mappings);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['userId', 'name', 'email', 'mobile_no', 'designation', 'is_active', 'bypass_otp', 'allow_multi_session', 'mappings']);

        if ($id) {
            $user = User::with(['RoleSchemeOfficeMappings.Scheme', 'RoleSchemeOfficeMappings.Role', 'mappedPermissions'])->findOrFail($id);
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->mobile_no = $user->mobile_no;
            $this->designation = $user->designation ?? '';
            $this->is_active = $user->is_active;
            $this->bypass_otp = $user->bypass_otp ?? false;
            $this->allow_multi_session = $user->allow_multi_session ?? false;

            foreach ($user->RoleSchemeOfficeMappings as $mapping) {
                $schemePermissions = $user->mappedPermissions->where('pivot.scheme_id', $mapping->scheme_id)->pluck('name')->toArray();
                
                $this->mappings[] = [
                    'id' => $mapping->id,
                    'scheme_id' => $mapping->scheme_id,
                    'role_id' => $mapping->role_id,
                    'office_id' => $mapping->office_id ?? '',
                    'permissions' => $schemePermissions
                ];
            }
        }

        if (empty($this->mappings)) {
            $this->addMapping();
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

        DB::transaction(function () {
            if ($this->userId) {
                $user = User::findOrFail($this->userId);
                $user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'mobile_no' => $this->mobile_no,
                    'designation' => $this->designation,
                    'is_active' => $this->is_active,
                    'bypass_otp' => $this->bypass_otp,
                    'allow_multi_session' => $this->allow_multi_session,
                ]);
            } else {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'mobile_no' => $this->mobile_no,
                    'designation' => $this->designation,
                    'is_active' => $this->is_active,
                    'bypass_otp' => $this->bypass_otp,
                    'allow_multi_session' => $this->allow_multi_session,
                    'password' => Hash::make(Str::random(12)),
                ]);
                $this->userId = $user->id;
            }

            $existingMappingIds = UserRoleSchemeOfficeMapping::where('user_id', $user->id)->pluck('id')->toArray();
            $keptMappingIds = [];

            foreach ($this->mappings as $mappingData) {
                if (!empty($mappingData['id'])) {
                    $mapping = UserRoleSchemeOfficeMapping::find($mappingData['id']);
                    if ($mapping) {
                        $mapping->update([
                            'scheme_id' => $mappingData['scheme_id'],
                            'role_id' => $mappingData['role_id'],
                            'office_id' => empty($mappingData['office_id']) ? null : $mappingData['office_id'],
                        ]);
                        $keptMappingIds[] = $mapping->id;
                    }
                } else {
                    $mapping = UserRoleSchemeOfficeMapping::create([
                        'user_id' => $user->id,
                        'scheme_id' => $mappingData['scheme_id'],
                        'role_id' => $mappingData['role_id'],
                        'office_id' => empty($mappingData['office_id']) ? null : $mappingData['office_id'],
                    ]);
                    $keptMappingIds[] = $mapping->id;
                }
            }

            $toDelete = array_diff($existingMappingIds, $keptMappingIds);
            if (!empty($toDelete)) {
                UserRoleSchemeOfficeMapping::whereIn('id', $toDelete)->delete();
            }

            $user->mappedPermissions()->detach();

            foreach ($this->mappings as $mappingData) {
                if (!empty($mappingData['permissions'])) {
                    $permissions = Permission::whereIn('name', $mappingData['permissions'])->get();
                    foreach ($permissions as $permission) {
                        $user->givePermissionWithScheme($permission->id, $mappingData['scheme_id']);
                    }
                }
            }
        });

        session()->flash('message', 'User saved successfully.');
        $this->closeModal();
    }

    public function with(): array
    {
        $query = User::with(['RoleSchemeOfficeMappings.Scheme', 'RoleSchemeOfficeMappings.Role'])->latest();
        
        if ($this->filterRoleId || $this->filterOfficeTypeId || $this->filterStateId || $this->filterDistrictId || $this->filterSubdivisionId || $this->filterBlockId || $this->filterOfficeId) {
            $query->whereHas('RoleSchemeOfficeMappings', function ($q) {
                if ($this->filterRoleId) $q->where('role_id', $this->filterRoleId);
                
                if ($this->filterOfficeTypeId || $this->filterStateId || $this->filterDistrictId || $this->filterSubdivisionId || $this->filterBlockId || $this->filterOfficeId) {
                    $q->whereHas('Office', function ($oq) {
                        if ($this->filterOfficeTypeId) $oq->where('office_type_id', $this->filterOfficeTypeId);
                        if ($this->filterStateId) $oq->where('state_id', $this->filterStateId);
                        if ($this->filterDistrictId) $oq->where('district_id', $this->filterDistrictId);
                        if ($this->filterSubdivisionId) $oq->where('subdivision_id', $this->filterSubdivisionId);
                        if ($this->filterBlockId) $oq->where('block_id', $this->filterBlockId);
                        if ($this->filterOfficeId) $oq->where('id', $this->filterOfficeId);
                    });
                }
            });
        }
        
        $officesQuery = OfficeMaster::where('is_active', true);
        if ($this->filterOfficeTypeId) $officesQuery->where('office_type_id', $this->filterOfficeTypeId);
        if ($this->filterStateId) $officesQuery->where('state_id', $this->filterStateId);
        if ($this->filterDistrictId) $officesQuery->where('district_id', $this->filterDistrictId);
        if ($this->filterSubdivisionId) $officesQuery->where('subdivision_id', $this->filterSubdivisionId);
        if ($this->filterBlockId) $officesQuery->where('block_id', $this->filterBlockId);
        
        $officeTypesQuery = Codemaster::where('parent_short_code', 'office_type');
        if ($this->filterRoleId) {
            $mappedOfficeTypeIds = RoleOfficeTypeMapping::where('role_id', $this->filterRoleId)->pluck('office_type_id');
            $officeTypesQuery->whereIn('code', $mappedOfficeTypeIds);
        }
        
        $rolesQuery = Role::query();
        if ($this->filterOfficeTypeId) {
            $mappedRoleIds = RoleOfficeTypeMapping::where('office_type_id', $this->filterOfficeTypeId)->pluck('role_id');
            $rolesQuery->whereIn('id', $mappedRoleIds);
        }

        return [
            'users' => $query->paginate(10),
            'schemes' => Scheme::where('is_active', true)->get(),
            'roles' => $rolesQuery->get(),
            'permissions' => Permission::all(),
            'officeTypes' => $officeTypesQuery->get(),
            'states' => State::all(),
            'districts' => $this->filterStateId ? District::where('state_id', $this->filterStateId)->get() : District::all(),
            'subdivisions' => $this->filterDistrictId ? Subdivision::where('district_id', $this->filterDistrictId)->get() : Subdivision::all(),
            'blocks' => $this->filterDistrictId ? Block::where('district_id', $this->filterDistrictId)->get() : Block::all(),
            'offices' => $officesQuery->get(),
        ];
    }
}; ?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Users Management') }}
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

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Users</h3>
                        <button wire:click="openModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm whitespace-nowrap">
                            Add New User
                        </button>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 mb-6">
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Filter Users By Role & Region</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3">
                            <select wire:model.live="filterRoleId" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition-colors text-sm font-medium">
                                <option value="">All Roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>

                            <select wire:model.live="filterOfficeTypeId" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition-colors text-sm">
                                <option value="">All Office Types</option>
                                @foreach($officeTypes as $type)
                                    <option value="{{ $type->code }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            
                            <select wire:model.live="filterStateId" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition-colors text-sm">
                                <option value="">All States</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                            
                            <select wire:model.live="filterDistrictId" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition-colors text-sm">
                                <option value="">All Districts</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                            
                            <select wire:model.live="filterSubdivisionId" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition-colors text-sm">
                                <option value="">All Subdivisions</option>
                                @foreach($subdivisions as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                            
                            <select wire:model.live="filterBlockId" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition-colors text-sm">
                                <option value="">All Blocks</option>
                                @foreach($blocks as $block)
                                    <option value="{{ $block->id }}">{{ $block->name }}</option>
                                @endforeach
                            </select>

                            <select wire:model.live="filterOfficeId" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition-colors text-sm font-medium">
                                <option value="">All Offices</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">User</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Details</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300">Roles & Schemes</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @forelse($users as $user)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-900 dark:text-slate-200">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                                            @if(!$user->is_active)
                                                <span class="inline-flex mt-1 px-2 text-[10px] font-semibold rounded-full bg-rose-100 text-rose-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            <div><span class="font-medium text-slate-500">Mob:</span> {{ $user->mobile_no }}</div>
                                            @if($user->designation)<div><span class="font-medium text-slate-500">Desig:</span> {{ $user->designation }}</div>@endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            <div class="flex flex-col gap-1.5">
                                                @forelse($user->RoleSchemeOfficeMappings as $mapping)
                                                    <div class="inline-flex items-center gap-1.5 flex-wrap">
                                                        <span class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-800 shadow-sm">{{ $mapping->Role->name ?? 'No Role' }}</span>
                                                        <span class="text-xs text-slate-400">in</span>
                                                        <span class="px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 rounded border border-emerald-200 dark:border-emerald-800 shadow-sm">{{ $mapping->Scheme->short_name ?? $mapping->Scheme->name ?? 'No Scheme' }}</span>
                                                    </div>
                                                @empty
                                                    <span class="text-xs text-slate-400 italic">No Roles Assigned</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right space-x-3">
                                            <button wire:click="openModal({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition-colors">Edit</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                <p>No users found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $users->links() }}
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
                 class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-4xl w-full mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]">
                
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $userId ? 'Edit User' : 'Create User' }}</h3>
                    <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors rounded-full p-1 hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-6 overflow-y-auto space-y-6">
                        <!-- Basic Info Section -->
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">Basic Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="name" :value="__('Full Name')" class="text-slate-700 dark:text-slate-300" />
                                    <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="email" :value="__('Email Address')" class="text-slate-700 dark:text-slate-300" />
                                    <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="mobile_no" :value="__('Mobile Number')" class="text-slate-700 dark:text-slate-300" />
                                    <x-text-input wire:model="mobile_no" id="mobile_no" class="block mt-1 w-full" type="text" required />
                                    <x-input-error :messages="$errors->get('mobile_no')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="designation" :value="__('Designation')" class="text-slate-700 dark:text-slate-300" />
                                    <x-text-input wire:model="designation" id="designation" class="block mt-1 w-full" type="text" />
                                    <x-input-error :messages="$errors->get('designation')" class="mt-1" />
                                </div>
                                <div class="col-span-1 md:col-span-2 mt-2 space-y-2">
                                    <div>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Active Account</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" wire:model="bypass_otp" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Bypass 2-Step Verification (OTP)</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" wire:model="allow_multi_session" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Allow Multi-Browser Login</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignments Section -->
                        <div>
                            <div class="flex justify-between items-end mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Role & Scheme Assignments</h4>
                                <button type="button" wire:click="addMapping" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Add Assignment
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                @foreach($mappings as $index => $mapping)
                                    <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg p-4 relative" wire:key="mapping-{{ $index }}">
                                        @if(count($mappings) > 1)
                                        <button type="button" wire:click="removeMapping({{ $index }})" class="absolute top-3 right-3 text-slate-400 hover:text-rose-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                        @endif
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div>
                                                <x-input-label :value="__('Scheme')" class="text-xs" />
                                                <select wire:model="mappings.{{ $index }}.scheme_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" required>
                                                    <option value="">Select Scheme</option>
                                                    @foreach($schemes as $scheme)
                                                        <option value="{{ $scheme->id }}">{{ $scheme->name }}</option>
                                                    @endforeach
                                                </select>
                                                <x-input-error :messages="$errors->get('mappings.'.$index.'.scheme_id')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('Role')" class="text-xs" />
                                                <select wire:model.live="mappings.{{ $index }}.role_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" required>
                                                    <option value="">Select Role</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                                <x-input-error :messages="$errors->get('mappings.'.$index.'.role_id')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('Office')" class="text-xs" />
                                                <select wire:model.live="mappings.{{ $index }}.office_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" required>
                                                    <option value="">Select Office Type</option>
                                                    @if(!empty($mapping['role_id']))
                                                        @foreach($this->getOfficesForRole($mapping['role_id']) as $office)
                                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <x-input-error :messages="$errors->get('mappings.'.$index.'.office_id')" class="mt-1" />
                                            </div>
                                            
                                            <div>
                                                <x-input-label :value="__('Office Address')" class="text-xs" />
                                                @php
                                                    $selectedOffice = null;
                                                    if (!empty($mapping['role_id']) && !empty($mapping['office_id'])) {
                                                        $selectedOffice = collect($this->getOfficesForRole($mapping['role_id']))->firstWhere('id', $mapping['office_id']);
                                                    }
                                                @endphp
                                                <x-text-input type="text" readonly class="mt-1 block w-full bg-slate-100 dark:bg-slate-800/80 text-sm opacity-70 cursor-not-allowed text-slate-600 dark:text-slate-400" 
                                                    value="{{ $selectedOffice ? $selectedOffice->address : '' }}" placeholder="Address will auto-populate..." />
                                            </div>
                                            
                                            <!-- Additional direct permissions -->
                                            <div class="col-span-1 md:col-span-4 mt-2 border-t border-slate-200 dark:border-slate-700 pt-3">
                                                <div x-data="{ expanded: false }">
                                                    <button type="button" @click="expanded = !expanded" class="text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 flex items-center gap-1">
                                                        <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                        Additional Direct Permissions (Optional)
                                                    </button>
                                                    <div x-show="expanded" x-collapse class="mt-3">
                                                        <div class="max-h-48 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded p-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                            @foreach($permissions as $permission)
                                                                <label class="flex items-start space-x-2 p-1 hover:bg-slate-50 dark:hover:bg-slate-800 rounded cursor-pointer">
                                                                    <input type="checkbox" wire:model="mappings.{{ $index }}.permissions" value="{{ $permission->name }}" class="mt-0.5 rounded border-slate-300 text-amber-500 focus:ring-amber-500 dark:bg-slate-800">
                                                                    <span class="text-xs text-slate-700 dark:text-slate-300 break-all leading-tight">{{ $permission->name }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-end gap-3 mt-auto">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-sm transition-colors flex items-center gap-2">
                            <span wire:loading wire:target="save" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                            {{ $userId ? 'Update User' : 'Save User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endvolt
</x-app-layout>
