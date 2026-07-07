<?php

namespace App\Livewire\DynamicWorkflow;

use App\Models\Codemaster;
use App\Models\DynamicWorkflowLabel;
use App\Models\DynamicWorkflowModule;
use App\Models\DynamicWorkflowSchemeModule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Scheme;
use App\Models\User;
use App\Models\UserRoleSchemeOfficeMapping;
use App\Models\WorkflowsteproleMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class WorkflowWizard extends Component
{
    public $currentTab = 1;

    public $totalTabs = 3;

    public $selectedScheme;

    public $selectedModule;

    public $isNewModule = false;

    public $newModuleName;

    public $newModuleCode;

    public $moduleList = [];

    public $stepCount = 1;

    public $stepNames = [];

    public $finalSteps = [];

    public $roles = [];

    public $permissionsList = [];

    public function mount()
    {
        $this->roles = Role::orderBy('name')->pluck('name', 'id')->toArray();
        $this->permissionsList = Permission::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function updatedSelectedScheme()
    {
        // $this->moduleList = DynamicWorkflowModule::where('scheme_id', $this->selectedScheme)->get();
        $this->moduleList = DynamicWorkflowModule::all();
        $this->selectedModule = null;
        $this->isNewModule = false;
    }

    public function updatedSelectedModule($value)
    {
        if ($value && $value != 'new') {
            $schemeModule = DynamicWorkflowSchemeModule::where('module_id', $value)
                ->where('scheme_id', $this->selectedScheme)
                ->first();

            if ($schemeModule) {
                $this->stepCount = $schemeModule->step_count;

                $labels = DynamicWorkflowLabel::where('module_id', $schemeModule->id)->where('scheme_id', $this->selectedScheme)
                    ->orderBy('id', 'asc')
                    ->pluck('label_name')
                    ->toArray();

                $this->stepNames = $labels;
            } else {
                $this->stepCount = 1;
                $this->stepNames = [];
            }
        }
    }

    public function incrementStepCount()
    {
        if ($this->stepCount < 10) {
            $this->stepCount++;
            $this->updateStepNames();
        }
    }

    public function decrementStepCount()
    {
        if ($this->stepCount > 1) {
            $this->stepCount--;
            $this->updateStepNames();
        }
    }

    public function updatedStepCount($value)
    {
        $this->stepCount = (int) $value;
        $this->updateStepNames();
    }

    protected function updateStepNames()
    {
        if ($this->stepCount < 1) {
            $this->stepCount = 1;
        }

        if ($this->stepCount > 10) {
            $this->stepCount = 10;
        }

        $newStepNames = [];

        for ($i = 0; $i < $this->stepCount; $i++) {
            $newStepNames[$i] = $this->stepNames[$i] ?? '';
        }

        $this->stepNames = $newStepNames;
    }

    public function moveToNaming()
    {
        if ($this->isNewModule) {
            $this->validate([
                'newModuleName' => 'required|min:3',
                'newModuleCode' => 'required|unique:dynamic_workflow_modules,module_code',
            ]);
        } else {
            $this->validate(['selectedModule' => 'required']);
        }

        $this->currentTab = 2;

        if (empty($this->stepNames)) {
            $this->stepNames = array_fill(0, $this->stepCount, '');
        }
    }

    public function moveToConfig()
    {
        $this->validate([
            'stepCount' => 'required|integer|min:1',
            'stepNames.*' => 'required',
        ]);
        $this->currentTab = 3;
        $existingMappings = collect();
        $existingLabels = collect();

        if (! $this->isNewModule) {
            $schemeModule = DynamicWorkflowSchemeModule::where('module_id', $this->selectedModule)
                ->where('scheme_id', $this->selectedScheme)
                ->first();

            if ($schemeModule) {
                $existingMappings = WorkflowsteproleMapping::where('module_id', $schemeModule->id)->where('scheme_id', $this->selectedScheme)
                    ->orderBy('rank', 'asc')
                    ->get()
                    ->groupBy('rank');

                $existingLabels = DynamicWorkflowLabel::where('module_id', $schemeModule->id)->where('scheme_id', $this->selectedScheme)
                    ->get()
                    ->keyBy('label_name');
            }
        }
        $this->finalSteps = [];
        foreach ($this->stepNames as $index => $label) {
            $rank = ($index + 1) * 10;
            $mappings = $existingMappings->get($rank, collect());
            $firstMapping = $mappings->first();

            $existingLabel = $existingLabels->get($label);
            $this->finalSteps[$index] = [
                'rank' => (int) $rank,
                'label' => $label,
                'permissions' => $existingLabel ? (array) ($existingLabel->permissions ?? []) : [],
                'role_ids' => $mappings
                    ->pluck('role_id')
                    ->map(fn($roleId) => (string) $roleId)
                    ->values()
                    ->all(),
                'is_final' => ($index == $this->stepCount - 1),
                'success_rank' => $firstMapping?->next_level_role_id ?? (($index < $this->stepCount - 1) ? ($index + 2) * 10 : null),
                'revert_rank' => $firstMapping?->same_level_role_id ?? (($index > 0) ? $index * 10 : null),
            ];
        }
    }

    public function saveWorkflow()
    {
        $this->validate([
            'selectedScheme' => 'required',
            'finalSteps.*.role_ids' => 'required|array|min:1',
            'finalSteps.*.role_ids.*' => 'exists:roles,id',
            'finalSteps.*.permissions' => 'required|array|min:1',
        ]);
        if (! Auth::check()) {
            session()->flash('error', 'Authentication session expired. Please login again.');

            return;
        }
        DB::beginTransaction();
        try {
            if ($this->isNewModule) {
                $module = DynamicWorkflowModule::create([
                    'module_code' => $this->newModuleCode,
                    'module_name' => $this->newModuleName,
                    'created_by' => Auth::id(),
                ]);
                $schemeModule = DynamicWorkflowSchemeModule::create([
                    'scheme_id' => $this->selectedScheme,
                    'module_id' => $module->id,
                    'main_module_code' => $this->newModuleCode,
                    'step_count' => $this->stepCount,
                ]);
            } else {
                $module = DynamicWorkflowModule::find($this->selectedModule);
                $schemeModule = DynamicWorkflowSchemeModule::updateOrCreate(
                    [
                        'scheme_id' => $this->selectedScheme,
                        'module_id' => $module->id,
                    ],
                    [
                        'main_module_code' => $module->module_code,
                        'step_count' => $this->stepCount,
                    ]
                );
            }
            WorkflowsteproleMapping::where('module_id', $schemeModule->id)->where('scheme_id', $this->selectedScheme)->delete();
            DynamicWorkflowLabel::where('module_id', $schemeModule->id)->where('scheme_id', $this->selectedScheme)->delete();
            foreach ($this->finalSteps as $index => $stepData) {
                $rank = ($index + 1) * 10;
                $successRank = ($index < count($this->finalSteps) - 1) ? ($index + 2) * 10 : 0;
                $revertRank = ($index > 0) ? $index * 10 : - ($this->selectedScheme);
                // Find the parent ID for dynamic_op_type
                $parent = Codemaster::where('short_name', 'dynamic_op_type')->first();
                $opTypeId = null;
                if ($parent) {
                    $labelSlug = strtolower(str_replace(' ', '_', $stepData['label']));
                    // Check if a codemaster already exists for this label under the parent
                    $codemaster = Codemaster::where('parent_id', $parent->id)
                        ->where('short_name', $labelSlug)
                        ->first();
                    if (! $codemaster) {
                        $maxCode = Codemaster::where('parent_short_code', 'dynamic_op_type')->max('code');
                        if (! $maxCode) {
                            $maxCode = ($parent->code * 10);
                        }
                        $codemaster = Codemaster::create([
                            'name' => strtoupper($stepData['label']),
                            'short_name' => strtolower($module->module_code) . '_' . $labelSlug,
                            'parent_id' => $parent->id,
                            'parent_short_code' => $parent->short_name,
                            'code' => $maxCode + 1,
                            'is_active' => 1,
                        ]);
                    }
                    $opTypeId = $codemaster->id;
                }

                $label = DynamicWorkflowLabel::create([
                    'scheme_id' => $this->selectedScheme,
                    'module_id' => $schemeModule->id,
                    'label_name' => $stepData['label'],
                    'op_type_id' => $opTypeId,
                ]);
                $savedPermissionIds = [];
                foreach ($stepData['role_ids'] as $roleId) {
                    WorkflowsteproleMapping::create([
                        'scheme_id' => $this->selectedScheme,
                        'module_id' => $schemeModule->id,
                        'workflow_step_id' => $label->id,
                        'role_id' => $roleId,
                        'rank' => $rank,
                        'next_level_role_id' => $successRank,
                        'same_level_role_id' => $revertRank,
                        'is_final_step' => ($index == count($this->finalSteps) - 1),
                        'action_type' => null,
                    ]);

                    foreach ($stepData['permissions'] as $permissionValue) {
                        if (! empty($permissionValue)) {
                            // If it's a numeric ID, find by ID, otherwise it might be a new name
                            if (is_numeric($permissionValue)) {
                                $permission = Permission::find($permissionValue);
                            } else {
                                $permission = Permission::firstOrCreate([
                                    'name' => $permissionValue,
                                    'guard_name' => 'web',
                                ]);
                            }

                            if ($permission) {
                                if (! in_array((string) $permission->id, $savedPermissionIds)) {
                                    $savedPermissionIds[] = (string) $permission->id;
                                }
                                $role = Role::find($roleId);
                                if ($role && ! $role->hasPermissionTo($permission->name)) {
                                    $role->givePermissionTo($permission);
                                }
                                // Assign permission to users mapped to this role and scheme
                                $userIds = UserRoleSchemeOfficeMapping::where('role_id', $roleId)
                                    ->where('scheme_id', $this->selectedScheme)
                                    ->where('is_active', 1)
                                    ->pluck('user_id');

                                foreach ($userIds as $userId) {
                                    $user = User::find($userId);
                                    if ($user) {

                                        $user->givePermissionWithScheme(
                                            $permission->id,
                                            $this->selectedScheme
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
                // Finally update the label with the exact list of chosen permission IDs
                $label->update(['permissions' => $savedPermissionIds]);
            }

            DB::commit();
            session()->flash('success', 'Workflow Master & Steps Configured Perfectly!');
            $this->reset(['selectedScheme', 'selectedModule', 'isNewModule', 'newModuleName', 'newModuleCode', 'stepCount', 'stepNames', 'finalSteps']);
            $this->currentTab = 1;
            $this->moduleList = [];
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function goBack()
    {
        if ($this->currentTab > 1) {
            $this->currentTab--;
        }
    }

    public function render()
    {
        return view('livewire.dynamic-workflow.workflow-wizard', [
            'schemes' => Scheme::where('is_active', true)->get(),
        ]);
    }
}
