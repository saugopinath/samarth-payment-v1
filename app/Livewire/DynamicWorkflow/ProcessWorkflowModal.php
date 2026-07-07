<?php

namespace App\Livewire\DynamicWorkflow;

use App\Models\DynamicWorkflowRequest;
use App\Models\WorkflowsteproleMapping;
use App\Models\DynamicWorkflowModule;
use App\Models\Scheme;
use App\Services\DynamicWorkflowService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class ProcessWorkflowModal extends Component
{
    public $isOpen = false;
    public $selectedRequest = null;
    public $remark, $SchemeName;
    public $button_status;

    #[On('openProcessModal')]
    public function openModal($requestId, $scheme_id, $module_id)
    {
        $this->selectedRequest = DynamicWorkflowRequest::with(['module', 'step.label', 'step.role'])
            ->where('scheme_id', $scheme_id)
            ->where('module_id', $module_id)
            ->find($requestId);
        // dd($this->selectedRequest->toSql(), $this->selectedRequest->getBindings(), $requestId);
        if (!$this->selectedRequest) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'Request not found'
            ]);
            return;
        }
        $step = WorkflowsteproleMapping::where('rank', $this->selectedRequest->current_rank)
            ->where('module_id', $this->selectedRequest->module_id)
            ->where('scheme_id', $this->selectedRequest->scheme_id)
            ->first();
        $this->SchemeName = Scheme::where('id', $scheme_id)->first()->name;
        if ($step) {
            $this->selectedRequest->setRelation('step', $step);
            $this->button_status = ($step->is_final_step == 1) ? 1 : 0;
        } else {
            $this->button_status = 0;
        }
        $this->remark = null;
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->selectedRequest = null;
        $this->remark = null;
    }

    public function processAction($action)
    {
        $this->validate([
            'remark' => 'required'
        ]);

        if (!$this->selectedRequest) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'No request selected'
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            $service = new DynamicWorkflowService();

            switch ($action) {
                case 'approve':
                    $result = $service->approve($this->selectedRequest->id, $this->remark);
                    break;
                case 'reject':
                    $result = $service->reject($this->selectedRequest->id, $this->remark);
                    break;
                default:
                    throw new \Exception('Invalid action');
            }

            DB::commit();

            $this->dispatch('toastr', [
                'type' => 'success',
                'message' => $result['message'] ?? 'Action successful'
            ]);
            $this->closeModal();
            $this->dispatch('refreshDatatable');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getGroupedChanges()
    {
        if (!$this->selectedRequest) return [];

        $fieldGroups = [
            'Name Update' => ['beneficiary_name'],
            'Date of Birth / Age Update' => ['dob', 'age'],
            'Mobile Update'     => ['mobile_no'],
            'Bank Update'     => ['bank_ifsc', 'bank_name', 'bank_branch_name', 'bank_account_number'],
        ];

        $newData = $this->selectedRequest->new_data ?? [];
        $oldData = $this->selectedRequest->old_data ?? [];
        $grouped = [];
        $processedFields = [];

        foreach ($fieldGroups as $groupName => $fields) {
            $groupChanges = array_intersect_key($newData, array_flip($fields));
            if (!empty($groupChanges)) {
                $grouped[$groupName] = [];
                foreach ($groupChanges as $field => $newValue) {
                    $grouped[$groupName][] = [
                        'label' => str_replace(['_', 'ifsc'], [' ', 'IFSC'], (string)$field),
                        'old'   => $oldData[$field] ?? 'N/A',
                        'new'   => $newValue,
                    ];
                    $processedFields[] = $field;
                }
            }
        }

        $otherChanges = array_diff_key($newData, array_flip($processedFields));
        if (!empty($otherChanges)) {
            $grouped['Other Changes'] = [];
            foreach ($otherChanges as $field => $newValue) {
                $grouped['Other Changes'][] = [
                    'label' => str_replace('_', ' ', (string)$field),
                    'old'   => $oldData[$field] ?? 'N/A',
                    'new'   => $newValue,
                ];
            }
        }

        return $grouped;
    }

    public function render()
    {
        return view('livewire.dynamic-workflow.process-workflow-modal', [
            'groupedChanges' => $this->getGroupedChanges()
        ]);
    }
}
