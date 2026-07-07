<?php

namespace App\Livewire\DynamicWorkflow;

use App\Models\DynamicWorkflowLabel;
use App\Models\DynamicWorkflowModule;
use App\Models\DynamicWorkflowRequest;
use App\Models\DynamicWorkflowSchemeModule;
use App\Models\UserRoleSchemeOfficeMapping;
use App\Models\WorkflowsteproleMapping;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\On;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class DynamicRequestTable extends DataTableComponent
{
    protected $model = DynamicWorkflowRequest::class;

    public string $moduleCode;   // main_module_code / module_code
    public int    $schemeId;
    public int    $schemeModuleId; // DynamicWorkflowSchemeModule.id
    public ?int   $selectedStepId = null; // Added: to filter by a specific step/label
    public int    $userRoleId = 0;
    public array  $filterCondition = [];

    public function mount(string $moduleCode, int $schemeId, int $schemeModuleId, ?int $selectedStepId = null): void
    {
        $this->moduleCode      = $moduleCode;
        $this->schemeId        = $schemeId;
        $this->schemeModuleId  = $schemeModuleId;
        $this->selectedStepId  = $selectedStepId;

        $lgd = session('lgd_session');
        if (!empty($lgd['role_id'])) {
            try {
                $this->userRoleId = (int) Crypt::decryptString($lgd['role_id']);
            } catch (\Exception) {
            }
        }
        if (!$this->userRoleId) {
            $this->userRoleId = (int) UserRoleSchemeOfficeMapping::where('user_id', Auth::id())
                ->where('is_active', 1)
                ->value('role_id') ?? 0;
        }
        if (!empty($lgd['district_id'])) {
            try {
                $this->filterCondition['created_by_dist_code'] = Crypt::decryptString($lgd['district_id']);
            } catch (\Exception) {
            }
        }
        if (!empty($lgd['block_id'])) {
            try {
                $this->filterCondition['created_by_local_body_code'] = Crypt::decryptString($lgd['block_id']);
            } catch (\Exception) {
            }
        }
        if (!empty($lgd['subdivision_id'])) {
            try {
                $this->filterCondition['created_by_local_body_code'] = Crypt::decryptString($lgd['subdivision_id']);
            } catch (\Exception) {
            }
        }
    }
    #[On('refreshDatatable')]
    public function refreshTable(): void
    {
        $this->dispatch('$refresh');
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setPaginationEnabled()
            ->setPerPageAccepted([10, 20, 50])
            ->setPerPage(10)
            ->setSearchEnabled()
            ->setSearchLive();

        $this->setHideBulkActionsWhenEmptyEnabled();

        $this->setTableWrapperAttributes([
            'class' => 'overflow-x-auto overflow-y-auto max-h-[500px] border rounded-lg shadow-sm',
        ]);

        $this->setTableAttributes([
            'class' => 'min-w-full text-sm text-gray-700 text-center overflow-x-auto',
        ]);

        $this->setTheadAttributes([
            'class' => 'bg-violet-800 text-xs uppercase py-3 px-4 text-white',
        ]);
        $this->setThAttributes(function ($column) {
            return [
                'class' => 'px-4 py-3 text-white bg-violet-800 text-xs',
            ];
        });

        $this->setTdAttributes(function ($row) {
            return [
                'class' => 'px-4 py-3 text-gray-700 text-center',
            ];
        });

        $this->setTbodyAttributes([
            'class' => 'px-4 py-3 divide-y divide-gray-200 bg-white overflow-y-auto',
        ]);
    }


    public function columns(): array
    {
        return [
            Column::make('Sl. No.', 'id')
                ->format(fn($value) => 'REF-' . $value),
            Column::make('Application ID', 'ref_id'),
            Column::make('Name')
                ->label(fn($row) => $row->beneficiary?->beneficiary_name ?? 'N/A'),
            Column::make('Changed Fields', 'changed_fields')
                ->format(function ($value) {
                    if (empty($value) || !is_array($value)) return '—';
                    $map = [
                        'beneficiary_name' => 'Name',
                        'dob_age'          => 'DOB / Age',
                        'mobile_no'        => 'Mobile',
                        'bank_details'     => 'Bank Details',
                    ];
                    $items = [];
                    foreach ($value as $i => $slug) {
                        $items[] = '<span class="font-medium">' . ($i + 1) . '. ' . ($map[$slug] ?? ucfirst(str_replace('_', ' ', $slug))) . '</span>';
                    }
                    return implode('<br>', $items);
                })
                ->html(),
            Column::make('Submitted At', 'created_at')
                ->format(fn($value) => $value?->format('d M Y, h:i A') ?? '—'),
            Column::make('Current Status', 'current_step_id')
                ->format(function ($value, $row) {
                    $label = DynamicWorkflowLabel::find($value)?->label_name ?? '—';
                    return '<span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">'
                        . e($label)
                        . '</span>';
                })
                ->html(),
        ];
    }
    public function builder(): Builder
    {
        $assignedLabelIds = WorkflowsteproleMapping::where('role_id', $this->userRoleId)
            ->where('module_id', $this->schemeModuleId)
            ->where('scheme_id', $this->schemeId)
            ->pluck('workflow_step_id')
            ->unique()
            ->toArray();
        /** @var Builder $query */
        $query = DynamicWorkflowRequest::query()
            ->with(['module', 'beneficiary'])
            ->where('module_id', $this->schemeModuleId)
            ->where('scheme_id', $this->schemeId);
        if ($this->selectedStepId) {
            $query->where('current_step_id', $this->selectedStepId);
        } else {
            $query->whereIn('current_step_id', $assignedLabelIds);
        }
        if (!empty($this->filterCondition['created_by_dist_code'])) {
            $distCode = $this->filterCondition['created_by_dist_code'];
            $query->whereHas('beneficiary', fn($q) => $q->where('created_by_dist_code', $distCode));
        }
        if (!empty($this->filterCondition['created_by_local_body_code'])) {
            $lbCode = $this->filterCondition['created_by_local_body_code'];
            $query->whereHas('beneficiary', fn($q) => $q->where('created_by_local_body_code', $lbCode));
        }
        return $query->orderByDesc('created_at');
    }
}
