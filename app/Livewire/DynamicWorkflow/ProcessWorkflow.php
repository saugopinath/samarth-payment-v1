<?php

namespace App\Livewire\DynamicWorkflow;

use App\Models\DynamicWorkflowRequest;
use App\Models\WorkflowsteproleMapping;
use App\Models\DynamicWorkflowLog;
use App\Models\BeneficiaryPersonalDetail;
use App\Models\DynamicWorkflowModule;
use App\Services\DynamicWorkflowService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcessWorkflow extends Component
{
    public $requests = [];
    public $selectedRequest = null;
    public $remark;
    public $module_code;
    public $module_id;
    public $module_scheme_id;
    public $button_status;
    public function mount()
    {
        // $this->module_code=request()->module_code;
        $this->module_code = config('constants.module_codes.update_mark_beneficiary');
        $module = DynamicWorkflowModule::where('module_code', $this->module_code)->first();
        if ($module) {
            $this->module_id = $module->id;
            $this->module_scheme_id = $module->scheme_id;
        }
        $this->loadRequests();
    }

    public function loadRequests()
    {
        $lgd_session = session('lgd_session');
        $userRoleId = 0;

        if (!empty($lgd_session['role_id'])) {
            try {
                $userRoleId = (int) \Illuminate\Support\Facades\Crypt::decryptString($lgd_session['role_id']);
            } catch (\Exception $e) {
            }
        }
        if (!$userRoleId) {
            $userRoleId = (int) \App\Models\UserRoleSchemeOfficeMapping::where('user_id', Auth::id())
                ->where('is_active', 1)
                ->value('role_id') ?? 0;
        }
        $userRanks = WorkflowsteproleMapping::where('role_id', $userRoleId)
            ->where('module_id', $this->module_id)
            ->pluck('rank')
            ->toArray();
        if (empty($userRanks)) {
            $this->requests = [];
            return;
        }
        $this->requests = DynamicWorkflowRequest::whereIn('current_rank', $userRanks)
            ->where('module_id', $this->module_id)
            ->where('scheme_id', $this->module_scheme_id)
            ->with(['module', 'step.label', 'step.role'])
            ->get();

        // $this->requests = DynamicWorkflowRequest::whereIn('current_rank', $userRanks)
        //     ->where('module_id', $this->module_id)
        //     ->where('scheme_id', $this->module_scheme_id)
        //     ->get()
        //     ->map(function ($req) {
        //         $req->step = WorkflowsteproleMapping::where('rank', $req->current_rank)
        //             ->where('module_id', $req->module_id)
        //             ->where('scheme_id', $req->scheme_id)
        //             ->first();

        //         return $req;
        //     });
    }
    public function viewDetails($requestId)
    {
        $this->selectedRequest = DynamicWorkflowRequest::with(['module', 'step.label', 'step.role'])
            ->find($requestId);

        if (!$this->selectedRequest) {
            return;
        }

        $step = WorkflowsteproleMapping::where('rank', $this->selectedRequest->current_rank)
            ->where('module_id', $this->selectedRequest->module_id)
            ->where('scheme_id', $this->selectedRequest->scheme_id)
            ->first();
        
        if ($step) {
            $this->selectedRequest->setRelation('step', $step);
            $this->button_status = ($step->is_final_step == 1) ? 1 : 0;
        } else {
            $this->button_status = 0;
        }
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

                case 'revert':
                    $result = $service->revert($this->selectedRequest->id, $this->remark);
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
            $this->selectedRequest = null;
            $this->remark = null;
            $this->loadRequests();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    public function render()
    {
        return view('livewire.dynamic-workflow.process-workflow');
    }
}
