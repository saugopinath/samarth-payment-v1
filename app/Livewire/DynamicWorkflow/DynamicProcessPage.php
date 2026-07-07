<?php

namespace App\Livewire\DynamicWorkflow;

use App\Models\DynamicWorkflowModule;
use App\Models\DynamicWorkflowSchemeModule;
use App\Models\Scheme;
use App\Models\UserRoleSchemeOfficeMapping;
use App\Models\WorkflowsteproleMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class DynamicProcessPage extends Component
{
    public ?string $moduleCode = null;   // e.g. 'UP_MB_D_01' passed from view
    public int $step = 1;   // 1 = scheme, 2 = module (skipped if preset), 3 = table
    public ?int    $selectedSchemeId   = null;
    public ?int    $selectedModuleId   = null;    // DynamicWorkflowSchemeModule.id
    public ?string $selectedModuleCode = null;    // resolved module_code for DataTable
    public ?string $selectedModuleName = null;    // display label

    // ─── Dropdown data ───────────────────────────────────────────────────────
    public array $schemes       = [];
    public array $moduleOptions = [];   // [id => label] shown only when not preset

    // ─── Flags ───────────────────────────────────────────────────────────────
    public bool $modulePreset = false;  // true when controller fixed the moduleCode

    // ─── Internal (not serialised) ───────────────────────────────────────────
    protected ?int $userRoleId = null;

    // ─────────────────────────────────────────────────────────────────────────

    public function mount(?string $moduleCode = null): void
    {
        if ($moduleCode) {
            $this->moduleCode   = $moduleCode;
            $this->modulePreset = true;
        }

        $this->loadSchemes();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function resolveUserRoleId(): int
    {
        if ($this->userRoleId) {
            return $this->userRoleId;
        }

        $lgd = session('lgd_session');
        if (!empty($lgd['role_id'])) {
            try {
                $this->userRoleId = (int) Crypt::decryptString($lgd['role_id']);
                return $this->userRoleId;
            } catch (\Exception) {
            }
        }

        $this->userRoleId = (int) UserRoleSchemeOfficeMapping::where('user_id', Auth::id())
            ->where('is_active', 1)
            ->value('role_id') ?? 0;

        return $this->userRoleId;
    }

    protected function loadSchemes(): void
    {
        $roleId = $this->resolveUserRoleId();

        if ($this->moduleCode) {
            // Only show schemes where THIS module is configured for the user's role
            $mainModule = DynamicWorkflowModule::where('module_code', $this->moduleCode)->first();
            if ($mainModule) {
                $smIds = DynamicWorkflowSchemeModule::where('module_id', $mainModule->id)
                    ->pluck('id')
                    ->toArray();

                $schemeIds = WorkflowsteproleMapping::where('role_id', $roleId)
                    ->whereIn('module_id', $smIds)
                    ->distinct()
                    ->pluck('scheme_id')
                    ->toArray();

                $this->schemes = Scheme::whereIn('id', $schemeIds)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray();
                return;
            }
        }

        // Default: all schemes where the role has any workflow mapping


        $this->schemes = Scheme::all()
            ->pluck('name', 'id')
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Lifecycle hooks
    // ─────────────────────────────────────────────────────────────────────────

    public function updatedSelectedSchemeId(?int $value): void
    {
        $this->reset(['selectedModuleId', 'selectedModuleCode', 'selectedModuleName', 'moduleOptions']);
        $this->step = 1;

        if (!$value) return;

        $roleId = $this->resolveUserRoleId();

        // Preset mode: auto-resolve schemeModule from the fixed moduleCode
        if ($this->moduleCode) {
            $mainModule = DynamicWorkflowModule::where('module_code', $this->moduleCode)->first();
            if ($mainModule) {
                $sm = DynamicWorkflowSchemeModule::where('module_id', $mainModule->id)
                    ->where('scheme_id', $value)
                    ->first();

                if ($sm) {
                    $this->selectedModuleId   = $sm->id;
                    $this->selectedModuleCode = $this->moduleCode;
                    $this->selectedModuleName = $mainModule->module_name ?? $sm->main_module_code;
                    $this->moduleOptions      = [$sm->id => $this->selectedModuleName];
                } else {
                    $this->dispatch('toastr', [
                        'type'    => 'error',
                        'message' => 'This module is not configured for the selected scheme.',
                    ]);
                }
            }
            return;
        }

        // Standard mode: list all modules the role can access in this scheme
        $mappedSmIds = WorkflowsteproleMapping::where('role_id', $roleId)
            ->where('scheme_id', $value)
            ->distinct()
            ->pluck('module_id')
            ->toArray();

        $schemeModules = DynamicWorkflowSchemeModule::with('module')
            ->where('scheme_id', $value)
            ->whereIn('id', $mappedSmIds)
            ->get();

        $this->moduleOptions = $schemeModules
            ->mapWithKeys(fn($sm) => [$sm->id => $sm->module->module_name ?? $sm->main_module_code])
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step actions
    // ─────────────────────────────────────────────────────────────────────────

    public function confirmScheme(): void
    {
        $this->validate(['selectedSchemeId' => 'required|integer']);

        if ($this->modulePreset) {
            if (!$this->selectedModuleId) {
                $this->dispatch('toastr', [
                    'type'    => 'error',
                    'message' => 'Module not configured for your role in this scheme.',
                ]);
                return;
            }
            // Skip module-select step → go straight to table
            $this->step = 3;
            return;
        }

        if (empty($this->moduleOptions)) {
            $this->dispatch('toastr', [
                'type'    => 'error',
                'message' => 'No module configured for your role in this scheme.',
            ]);
            return;
        }

        $this->step = 2;
    }

    public function confirmModule(): void
    {
        $this->validate(['selectedModuleId' => 'required|integer']);

        $sm = DynamicWorkflowSchemeModule::with('module')->find($this->selectedModuleId);
        if (!$sm) {
            $this->dispatch('toastr', ['type' => 'error', 'message' => 'Module not found.']);
            return;
        }

        $this->selectedModuleCode = $sm->main_module_code ?? $sm->module?->module_code;
        $this->selectedModuleName = $sm->module?->module_name ?? $sm->main_module_code;
        $this->step = 3;
    }

    public function goBack(): void
    {
        if ($this->step === 3) {
            $this->reset(['selectedModuleId', 'selectedModuleCode', 'selectedModuleName']);
            // If preset, go back to scheme; if not, go back to module select
            $this->step = $this->modulePreset ? 1 : 2;
        } elseif ($this->step === 2) {
            $this->reset(['selectedModuleId', 'selectedModuleCode', 'selectedModuleName', 'moduleOptions']);
            $this->step = 1;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.dynamic-workflow.dynamic-process-page');
    }
}
