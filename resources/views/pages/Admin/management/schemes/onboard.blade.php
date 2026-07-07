<?php use function Laravel\Folio\{name, middleware}; name('management.schemes.onboard'); middleware(['auth', 'verified']); ?>
<?php

use App\Models\Scheme;
use App\Models\Department;
use App\Models\Role;
use App\Models\Permission;
use App\Models\DupcheckschemeconfigSetting;
use App\Models\SchemeCapacity;
use App\Models\District;
use App\Models\Block;
use App\Models\Subdivision;
use App\Models\DynamicWorkflowModule;
use App\Models\DynamicWorkflowSchemeModule;
use App\Models\DynamicWorkflowLabel;
use App\Models\WorkflowsteproleMapping;
use App\Models\Codemaster;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public $currentStep = 1;
    public $totalSteps = 5;

    // Step 1: Basic Config
    public $name = '';
    public $short_name = '';
    public $description = '';
    public $department_id = '';
    public $base_amount = '';

    // Step 2: Date/Age Config
    public $min_age = '';
    public $max_age = '';

    // Step 3: Workflow Config
    public $workflow_steps = [];
    public $module_code = '';

    // Step 4: Duplicate Config
    public $dup_is_same = true;
    public $dup_is_cross = false;
    public $dup_check_with = []; // e.g. ['aadhar', 'bank_account', 'mobile_no']
    public $dup_scheme_lists = []; // For cross-scheme

    // Step 5: Capacity/Quotas
    public $quotas = [];
    
    // Aux
    public $departments = [];
    public $roles = [];
    public $permissionsList = [];
    public $allSchemes = [];
    
    public $checkOptions = [
        'aadhar_no' => 'Aadhaar Number',
        'bank_account_no' => 'Bank Account Number',
        'mobile_no' => 'Mobile Number'
    ];

    public $geoModels = [
        'App\Models\District' => 'District',
        'App\Models\Block' => 'Block',
        'App\Models\Subdivision' => 'Subdivision'
    ];

    public $actionTypes = [
        'entry' => 'Entry',
        'verification' => 'Verification',
        'approval' => 'Approval'
    ];

    public function mount()
    {
        $this->departments = Department::all();
        $this->roles = Role::orderBy('name')->get();
        $this->permissionsList = Permission::orderBy('name')->get();
        $this->allSchemes = Scheme::where('is_active', true)->get();

        $this->addWorkflowStep();
        $this->addQuota();
    }

    public function addWorkflowStep()
    {
        $this->workflow_steps[] = [
            'label' => '',
            'role_ids' => [],
            'permissions' => []
        ];
    }

    public function removeWorkflowStep($index)
    {
        unset($this->workflow_steps[$index]);
        $this->workflow_steps = array_values($this->workflow_steps);
    }

    public function addQuota()
    {
        $this->quotas[] = [
            'model_type' => 'App\Models\District',
            'model_id' => '', // 0 or null for all instances of that model, or a specific ID
            'action_type' => 'entry',
            'total_capacity' => 100
        ];
    }

    public function removeQuota($index)
    {
        unset($this->quotas[$index]);
        $this->quotas = array_values($this->quotas);
    }

    public function nextStep()
    {
        if ($this->currentStep == 1) {
            $this->validate([
                'name' => 'required|string|max:255',
                'short_name' => 'required|string|max:50|unique:schemes,short_name',
                'department_id' => 'required|exists:departments,id',
                'base_amount' => 'required|numeric|min:0'
            ]);
        } elseif ($this->currentStep == 2) {
            $this->validate([
                'min_age' => 'nullable|integer|min:0|max:150',
                'max_age' => 'nullable|integer|min:0|max:150|gte:min_age'
            ]);
        } elseif ($this->currentStep == 3) {
            $this->validate([
                'module_code' => 'required|string|max:50|unique:dynamic_workflow_modules,module_code',
                'workflow_steps' => 'required|array|min:1',
                'workflow_steps.*.label' => 'required|string',
                'workflow_steps.*.role_ids' => 'required|array|min:1'
            ]);
        } elseif ($this->currentStep == 4) {
            $this->validate([
                'dup_check_with' => 'required|array|min:1',
                'dup_scheme_lists' => 'required_if:dup_is_cross,true|array'
            ]);
        }

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function saveScheme()
    {
        $this->validate([
            'quotas' => 'array',
            'quotas.*.total_capacity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            // 1 & 2. Create Scheme
            $scheme = Scheme::create([
                'name' => $this->name,
                'short_name' => $this->short_name,
                'description' => $this->description,
                'department_id' => $this->department_id,
                'base_amount' => $this->base_amount,
                'min_age' => empty($this->min_age) ? null : $this->min_age,
                'max_age' => empty($this->max_age) ? null : $this->max_age,
                'is_active' => true
            ]);

            // 3. Workflow Configuration
            $module = DynamicWorkflowModule::create([
                'module_code' => strtoupper($this->module_code),
                'module_name' => $this->name . ' Workflow',
                'created_by' => Auth::id(),
            ]);

            $schemeModule = DynamicWorkflowSchemeModule::create([
                'scheme_id' => $scheme->id,
                'module_id' => $module->id,
                'main_module_code' => $module->module_code,
                'step_count' => count($this->workflow_steps),
            ]);

            foreach ($this->workflow_steps as $index => $stepData) {
                $rank = ($index + 1) * 10;
                $successRank = ($index < count($this->workflow_steps) - 1) ? ($index + 2) * 10 : 0;
                $revertRank = ($index > 0) ? $index * 10 : -$scheme->id;

                $label = DynamicWorkflowLabel::create([
                    'scheme_id' => $scheme->id,
                    'module_id' => $schemeModule->id,
                    'label_name' => $stepData['label'],
                ]);

                foreach ($stepData['role_ids'] as $roleId) {
                    WorkflowsteproleMapping::create([
                        'scheme_id' => $scheme->id,
                        'module_id' => $schemeModule->id,
                        'workflow_step_id' => $label->id,
                        'role_id' => $roleId,
                        'rank' => $rank,
                        'next_level_role_id' => $successRank,
                        'same_level_role_id' => $revertRank,
                        'is_final_step' => ($index == count($this->workflow_steps) - 1),
                    ]);
                }
                
                // Add permissions (assuming basic string array structure or similar)
                // Simplified for this onboarding wizard
                $label->update(['permissions' => $stepData['permissions']]);
            }

            // 4. Duplicate Configuration
            DupcheckschemeconfigSetting::create([
                'scheme_id' => $scheme->id,
                'is_same' => $this->dup_is_same,
                'is_cross' => $this->dup_is_cross,
                'check_with' => implode(',', $this->dup_check_with),
                'scheme_lists' => $this->dup_is_cross ? $this->dup_scheme_lists : null
            ]);

            // 5. Capacity / Quotas
            foreach ($this->quotas as $quota) {
                SchemeCapacity::create([
                    'scheme_id' => $scheme->id,
                    'model_type' => $quota['model_type'],
                    'model_id' => empty($quota['model_id']) ? 0 : $quota['model_id'],
                    'action_type' => $quota['action_type'],
                    'capacity_type' => 2,
                    'total_capacity' => $quota['total_capacity'],
                    'is_active' => true
                ]);
            }

            DB::commit();
            session()->flash('success', 'Scheme onboarded successfully!');
            $this->redirect(route('management.schemes'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error onboarding scheme: ' . $e->getMessage());
        }
    }
}; ?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Scheme Onboarding Wizard') }}
        </h2>
    </x-slot>

    @volt
    <div>
        <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if (session()->has('error'))
                <div class="mb-4 bg-rose-100 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-0 flex flex-col md:flex-row min-h-[500px]">
                    <!-- Sidebar / Steps Indicator -->
                    <div class="w-full md:w-1/4 bg-slate-50 dark:bg-slate-900/50 p-6 border-r border-slate-200 dark:border-slate-700">
                        <ul class="space-y-6">
                            @foreach(['Basic Info & Amount', 'Date & Age Limits', 'Workflow Steps', 'Duplicate Checking', 'Quotas & Capacity'] as $index => $stepName)
                                <li class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 text-sm font-semibold transition-colors
                                        {{ $currentStep > ($index + 1) ? 'bg-emerald-500 border-emerald-500 text-white' : 
                                           ($currentStep == ($index + 1) ? 'border-amber-500 text-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-slate-300 text-slate-400') }}">
                                        @if($currentStep > ($index + 1))
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <span class="text-sm font-medium {{ $currentStep == ($index + 1) ? 'text-slate-900 dark:text-slate-100' : 'text-slate-500 dark:text-slate-400' }}">{{ $stepName }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Main Content Form -->
                    <div class="w-full md:w-3/4 p-6 flex flex-col relative">
                        <form wire:submit.prevent="saveScheme" class="flex-1 flex flex-col">
                            
                            <!-- Step 1: Basic Config -->
                            <div x-show="$wire.currentStep == 1">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4">Step 1: Scheme Basics & Amount</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="name" value="Scheme Name *" />
                                        <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="short_name" value="Short Name (Code) *" />
                                        <x-text-input wire:model="short_name" id="short_name" type="text" class="mt-1 block w-full" required />
                                        <x-input-error :messages="$errors->get('short_name')" class="mt-2" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-input-label for="department_id" value="Department *" />
                                        <select wire:model="department_id" id="department_id" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                            <option value="">Select Department</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-input-label for="description" value="Description" />
                                        <textarea wire:model="description" id="description" rows="2" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm"></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-input-label for="base_amount" value="Base Financial Amount (₹) *" />
                                        <x-text-input wire:model="base_amount" id="base_amount" type="number" step="0.01" class="mt-1 block w-full bg-emerald-50 dark:bg-emerald-900/10 text-emerald-700 dark:text-emerald-400 border-emerald-300" required />
                                        <x-input-error :messages="$errors->get('base_amount')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Date / Age Limits -->
                            <div x-show="$wire.currentStep == 2" style="display: none;">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4">Step 2: Date & Age Limits</h3>
                                <p class="text-sm text-slate-500 mb-4">Configure the minimum and maximum allowable age for applicants of this scheme.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border border-slate-200 dark:border-slate-700 p-4 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                                    <div>
                                        <x-input-label for="min_age" value="Minimum Age (Optional)" />
                                        <x-text-input wire:model="min_age" id="min_age" type="number" class="mt-1 block w-full" placeholder="e.g. 18" />
                                        <x-input-error :messages="$errors->get('min_age')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="max_age" value="Maximum Age (Optional)" />
                                        <x-text-input wire:model="max_age" id="max_age" type="number" class="mt-1 block w-full" placeholder="e.g. 60" />
                                        <x-input-error :messages="$errors->get('max_age')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Workflow Config -->
                            <div x-show="$wire.currentStep == 3" style="display: none;">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4">Step 3: Workflow Steps & Permissions</h3>
                                <p class="text-sm text-slate-500 mb-4">Define the workflow stages (e.g. Clerk Entry, BDO Verification, SDO Approval).</p>
                                
                                <div class="mb-4">
                                    <x-input-label for="module_code" value="Workflow Module Code (Unique) *" />
                                    <x-text-input wire:model="module_code" id="module_code" type="text" class="mt-1 block w-full" placeholder="e.g. OAP_FLOW_V1" required />
                                    <x-input-error :messages="$errors->get('module_code')" class="mt-2" />
                                </div>

                                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                                    @foreach($workflow_steps as $index => $step)
                                        <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-white dark:bg-slate-800 relative">
                                            @if(count($workflow_steps) > 1)
                                                <button type="button" wire:click="removeWorkflowStep({{ $index }})" class="absolute top-2 right-2 text-rose-500 hover:text-rose-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            @endif
                                            
                                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Level {{ $index + 1 }}</h4>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <x-input-label value="Step Name / Action Label *" />
                                                    <x-text-input wire:model="workflow_steps.{{ $index }}.label" type="text" class="mt-1 block w-full text-sm" placeholder="e.g. Entry, Verification, Approval" required />
                                                </div>
                                                <div>
                                                    <x-input-label value="Allowed Roles *" />
                                                    <select wire:model="workflow_steps.{{ $index }}.role_ids" multiple class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm h-24" required>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <x-input-label value="Required Permissions (Optional)" />
                                                    <select wire:model="workflow_steps.{{ $index }}.permissions" multiple class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm h-24">
                                                        @foreach($permissionsList as $perm)
                                                            <option value="{{ $perm->id }}">{{ $perm->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    <button type="button" wire:click="addWorkflowStep" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Add Another Level
                                    </button>
                                    <x-input-error :messages="$errors->get('workflow_steps')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Step 4: Duplicate Config -->
                            <div x-show="$wire.currentStep == 4" style="display: none;">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4">Step 4: Duplicate Data Checking</h3>
                                <p class="text-sm text-slate-500 mb-6">Configure which fields should be checked for duplicates to prevent fraud.</p>

                                <div class="space-y-6">
                                    <div>
                                        <x-input-label value="Check for Duplicates Using: *" class="mb-2" />
                                        <div class="flex flex-wrap gap-4">
                                            @foreach($checkOptions as $key => $label)
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" wire:model="dup_check_with" value="{{ $key }}" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <x-input-error :messages="$errors->get('dup_check_with')" class="mt-2" />
                                    </div>

                                    <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
                                        <div class="flex items-center gap-6 mb-4">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model="dup_is_same" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Check Within Same Scheme</span>
                                            </label>
                                            
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.live="dup_is_cross" class="rounded border-slate-300 text-amber-500 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Cross-Check with Other Schemes</span>
                                            </label>
                                        </div>

                                        <div x-show="$wire.dup_is_cross" x-transition class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-lg">
                                            <x-input-label value="Select Schemes for Cross-Checking *" />
                                            <select wire:model="dup_scheme_lists" multiple class="mt-1 block w-full border-amber-300 dark:border-amber-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm h-32">
                                                @foreach($allSchemes as $s)
                                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('dup_scheme_lists')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 5: Quotas -->
                            <div x-show="$wire.currentStep == 5" style="display: none;">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4">Step 5: Operational Quotas & Capacity</h3>
                                <p class="text-sm text-slate-500 mb-4">Set caps on how many applications can be processed at specific administrative levels.</p>

                                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                                    @foreach($quotas as $index => $quota)
                                        <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-white dark:bg-slate-800 relative">
                                            @if(count($quotas) > 1)
                                                <button type="button" wire:click="removeQuota({{ $index }})" class="absolute top-2 right-2 text-rose-500 hover:text-rose-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            @endif
                                            
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                <div>
                                                    <x-input-label value="Action / Stage *" />
                                                    <select wire:model="quotas.{{ $index }}.action_type" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" required>
                                                        @foreach($actionTypes as $val => $label)
                                                            <option value="{{ $val }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <x-input-label value="Geographic Level *" />
                                                    <select wire:model="quotas.{{ $index }}.model_type" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" required>
                                                        @foreach($geoModels as $val => $label)
                                                            <option value="{{ $val }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <x-input-label value="Total Capacity / Quota *" />
                                                    <x-text-input wire:model="quotas.{{ $index }}.total_capacity" type="number" min="1" class="mt-1 block w-full text-sm font-semibold text-amber-700" required />
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    <button type="button" wire:click="addQuota" class="text-sm font-medium text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Add Another Quota
                                    </button>
                                </div>
                            </div>

                            <!-- Footer Buttons -->
                            <div class="mt-auto pt-6 flex justify-between border-t border-slate-200 dark:border-slate-700">
                                @if($currentStep > 1)
                                    <button type="button" wire:click="prevStep" class="px-5 py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-medium rounded-lg transition-colors">
                                        Back
                                    </button>
                                @else
                                    <div></div>
                                @endif

                                @if($currentStep < $totalSteps)
                                    <button type="button" wire:click="nextStep" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
                                        Next Step
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                @else
                                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-md transition-colors flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Finalize & Onboard Scheme
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endvolt
</x-app-layout>
