<?php

namespace App\Livewire\DynamicWorkflow;

use App\Models\AgeManagements;
use App\Models\BeneficiaryPersonalDetail;
use App\Models\DynamicWorkflowModule;
use App\Models\DynamicWorkflowRequest;
use App\Models\DynamicWorkflowSchemeModule;
use App\Models\Ifsccodemaster;
use App\Models\Scheme;
use App\Models\UserRoleSchemeOfficeMapping;
use App\Models\WorkflowsteproleMapping;
use App\Services\DynamicWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Helpers\DuplicateChecker;

class RequestUpdateBeneficiary extends Component
{
    public $beneficiary = null;
    public $showFields = false;
    public $selectedFields = [];
    public $fieldOptions = [];
    public $availableModules = [];
    public $moduleId;
    public $moduleCode;
    public $moduleName;
    public $moduleSchemeId;
    public $currentRoleId;
    public $oldData = [];
    public $newData = [];
    public $items = [];
    public $RoleId;
    public $filter_condition = [];
    public $requestModuleCode;

    protected $listeners = [
        'beneficiary-search' => 'handleSearch',
        'reset-beneficiary-search' => 'resetSearch',
    ];

    protected array $baseFieldOptions = [
        'beneficiary_name' => 'Name Update',
        'dob_age' => 'Date of Birth / Age Update',
        'mobile_no' => 'Mobile Update',
        'bank_details' => 'Bank Update',
    ];

    public function mount($moduleCode = null, $moduleName = null, $moduleId = null)
    {
        // dd($moduleCode, $moduleName, $moduleId);
        $selectLgd = session('lgd_session');
        $this->requestModuleCode = $moduleCode;
        // $this->moduleName = $moduleName;
        // $this->moduleId = $moduleId;
        $this->currentRoleId = Crypt::decryptString($selectLgd['role_id']);
        if (!empty($selectLgd['district_id'])) {
            $this->filter_condition['created_by_dist_code'] = Crypt::decryptString($selectLgd['district_id']);
        }
        if (!empty($selectLgd['block_id'])) {
            $this->filter_condition['created_by_local_body_code'] = Crypt::decryptString($selectLgd['block_id']);
        }
        if (!empty($selectLgd['subdivision_id'])) {
            $this->filter_condition['created_by_local_body_code'] = Crypt::decryptString($selectLgd['subdivision_id']);
        }
        if (!empty($selectLgd['role_id'])) {
            $this->RoleId = Crypt::decryptString($selectLgd['role_id']);
        }
        // $module = DynamicWorkflowModule::where('module_code', $this->requestModuleCode)->first();
        // if (!$module) {
        //     abort(404, 'Module not found');
        // }
        // $this->moduleId = $module->id;
        // $this->moduleCode = $module->module_code;
        // $this->moduleSchemeId = $module->scheme_id;
        $this->fieldOptions = $this->baseFieldOptions;
    }

    public function handleSearch($data)
    {
        // dd($data);
        if (empty($data['results'])) {
            $this->items = [];
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'No matching approved beneficiary found.'
            ]);
            return;
        }
        $applicationIds = collect($data['results'])->pluck('application_id')->toArray();
        $this->moduleSchemeId = $data['results'][0]['scheme_id'];
        // dd($this->moduleSchemeId);
        // dd($this->requestModuleCode);
        $Mainmodule = DynamicWorkflowModule::where('module_code', $this->requestModuleCode)->first();
        // dd($Mainmodule);
        if (!$Mainmodule) {
            abort(404, 'Module not found');
        }
        $module = DynamicWorkflowSchemeModule::where('module_id', $Mainmodule->id)->where('scheme_id', $this->moduleSchemeId)->first();
        // dd($module);
        if (!$module) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'Steps are not configured for this scheme!'
            ]);
            return;
        }
        $firstStep = WorkflowsteproleMapping::where([
            'module_id' => $module->id,
            'scheme_id' => $this->moduleSchemeId,
            'role_id' => $this->RoleId,
        ])
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'asc')
            ->first();
        if (!$firstStep) {
            // dd($firstStep);
            // throw new \Exception('You are not authorized to initiate this workflow or steps are not configured.');
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'You are not authorized to initiate this workflow or steps are not configured.'
            ]);
            return;
        }
        $SubmittedRequest = DynamicWorkflowRequest::where('ref_id', $applicationIds)
            ->where('scheme_id', $this->moduleSchemeId)
            ->where('module_id', $this->moduleId)
            ->whereNotIn('current_rank', [-100, 0])
            ->get();
        // dd($SubmittedRequest);
        if ($SubmittedRequest->count() > 0) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'Request already Pending!'
            ]);
            return;
        }
        $this->items = BeneficiaryPersonalDetail::query()
            ->select(['application_id', 'beneficiary_id', 'scheme_id', 'beneficiary_name', 'other_details'])
            ->with([
                'contact:beneficiary_id,application_id,scheme_id,district_id,rural_urban,blockurban,gpward',
                'bank:beneficiary_id,application_id,scheme_id,bankaccountnumber,ifscode'
            ])
            ->whereIn('application_id', $applicationIds)
            ->get()
            ->map(fn($item) => [
                'application_id' => $item->application_id,
                'beneficiary_id' => $item->beneficiary_id,
                'applicant_name' => $item->beneficiary_name,
                'mobile_no'      => $item->other_details['mobile_no'] ?? '-',
                'address'        => optional($item->contact)->getFullAddress() ?? 'N/A',
                'bank_account'   => optional($item->bank)->bankaccountnumber ?? '-',
                'ifsc'           => optional($item->bank)->ifscode ?? '-',
                'scheme_id'      => $item->scheme_id,
            ])->toArray();
    }
    public function resetSearch()
    {
        $this->items = [];
        $this->beneficiary = null;
        $this->showFields = false;
        $this->selectedFields = [];
    }

    public function selectBeneficiary($appId)
    {
        // dd($appId);
        $this->resetValidation();
        // $this->selectedFields = [];
        $this->beneficiary = BeneficiaryPersonalDetail::with(['bank', 'contact'])
            ->where('application_id', $appId)
            //->where('scheme_id', $this->moduleSchemeId)
            ->first();
        // dd($this->beneficiary);
        if (!$this->beneficiary) {
            $this->dispatch('toast', 'error', 'Beneficiary not found');
            return;
        }
        $this->showFields = true;
        $this->hydrateBeneficiaryData();
    }

    public function submitRequest()
    {
        // dd('dfsf');
        $Mainmodule = DynamicWorkflowModule::where('module_code', $this->requestModuleCode)->first();
        if (!$Mainmodule) {
            abort(404, 'Module not found');
        }
        $module = DynamicWorkflowSchemeModule::where('module_id', $Mainmodule->id)->where('scheme_id', $this->beneficiary->scheme_id)->first();
        // dd($module);
        if (!$module) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'Steps are not configured for this scheme!'
            ]);
            return;
        }
        $this->moduleId = $module->id;
        $this->moduleCode = $module->module_code;
        // $this->moduleSchemeId = $module->scheme_id;
      
        if (!$this->beneficiary) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'No beneficiary selected!'
            ]);
            return;
        }
       
        if (empty($this->selectedFields)) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'Select at least one field!'
            ]);
            return;
        }
          
        $this->validate($this->rules(), [], $this->validationAttributes());
        
        $payload = $this->prepareWorkflowPayload();
        if (count($payload['actual_changed_blocks']) !== count($this->selectedFields)) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => 'Operation stopped: All selected fields must be updated with new values.'
            ]);
            return;
        }
        $checkData = [
            'mobile_no' => $this->newData['mobile_no'] ?? null,
            'bankaccountnumber' => $this->newData['bank_account_number'] ?? null,
        ];
        $duplicateResult = DuplicateChecker::check(
            (int)$this->beneficiary->scheme_id,
            (int)$this->beneficiary->application_id,
            $checkData
        );
        if ($duplicateResult !== true) {
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => $duplicateResult['message']
            ]);
            return;
        }
        $hasPendingRequest = DynamicWorkflowRequest::where('module_id', $this->moduleId)
            ->where('ref_id', $this->beneficiary->application_id)
            ->where('scheme_id', $this->beneficiary->scheme_id)
            ->whereNotIn('current_rank', [-100, 0]) // -100 = rejected, 0 = completed
            ->exists();
            //    dd($hasPendingRequest);
        if ($hasPendingRequest) {
            $this->dispatch('toast', 'error', 'A pending request already exists.');
            return;
        }
        
        DB::beginTransaction();
        try {
            $service = new DynamicWorkflowService();
            $newRequest = $service->initiateRequest(
                $this->moduleId,
                $this->beneficiary->application_id,
                $this->beneficiary->scheme_id,
                $payload['old'],
                $payload['new'],
                $payload['changed_fields']
            );
            DB::commit();
            $message = "Request submitted successfully! Request ID: " . $newRequest->id;

            $this->dispatch('toastr', [
                'type' => 'success',
                'message' => $message
            ]);
            return redirect()->route('request-update-beneficiary');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toastr', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
            // return redirect()->route('dynamic-workflow-request');
        }
    }

    public function updatedSelectedFields()
    {
        $this->resetValidation();
    }

    public function updatedNewDataDob($value)
    {
        if (blank($value)) {
            $this->newData['age'] = null;
            return;
        }

        try {
            $this->newData['age'] = Carbon::parse($value)->age;
        } catch (\Throwable $e) {
            $this->newData['age'] = null;
        }
    }

    public function updatedNewDataBankIfsc($value)
    {
        $ifsc = strtoupper(trim((string) $value));
        $this->newData['bank_ifsc'] = $ifsc;

        if ($ifsc === '') {
            $this->newData['bank_name'] = '';
            $this->newData['bank_branch_name'] = '';
            return;
        }

        if (strlen($ifsc) !== 11) {
            $this->newData['bank_name'] = '';
            $this->newData['bank_branch_name'] = '';
            return;
        }

        $ifscMaster = Ifsccodemaster::with('bankmaster')
            ->where('code', $ifsc)
            ->where('is_active', 1)
            ->first();

        if (!$ifscMaster) {
            $this->newData['bank_name'] = '';
            $this->newData['bank_branch_name'] = '';
            $this->addError('newData.bank_ifsc', 'This IFSC code is not registered.');
            return;
        }

        $this->resetErrorBag('newData.bank_ifsc');
        $this->newData['bank_name'] = $ifscMaster->bankmaster->name ?? '';
        $this->newData['bank_branch_name'] = $ifscMaster->branch ?? '';
    }

    protected function rules()
    {
        $rules = [];
        $ageConfig = AgeManagements::where('scheme_id', $this->moduleSchemeId)->first();

        if (in_array('beneficiary_name', $this->selectedFields, true)) {
            $rules['newData.beneficiary_name'] = ['required', 'string', 'max:150', 'regex:/^[a-zA-Z\\s\\.]+$/'];
        }

        if (in_array('dob_age', $this->selectedFields, true)) {
            $dobRules = ['required', 'date'];

            if ($ageConfig?->max_age !== null) {
                $dobRules[] = 'after_or_equal:' . now()->subYears($ageConfig->max_age)->format('Y-m-d');
            }

            if ($ageConfig?->min_age !== null) {
                $dobRules[] = 'before_or_equal:' . now()->subYears($ageConfig->min_age)->format('Y-m-d');
            }

            $rules['newData.dob'] = $dobRules;

            $ageRules = ['required', 'integer'];

            if ($ageConfig?->min_age !== null) {
                $ageRules[] = 'min:' . $ageConfig->min_age;
            }

            if ($ageConfig?->max_age !== null) {
                $ageRules[] = 'max:' . $ageConfig->max_age;
            }

            $rules['newData.age'] = $ageRules;
        }

        if (in_array('mobile_no', $this->selectedFields, true)) {
            $rules['newData.mobile_no'] = ['required', 'digits:10'];
        }

        if (in_array('bank_details', $this->selectedFields, true)) {
            $rules['newData.bank_ifsc'] = ['required', 'string', 'size:11'];
            $rules['newData.bank_name'] = ['required', 'string', 'max:150'];
            $rules['newData.bank_branch_name'] = ['required', 'string', 'max:150'];
            $rules['newData.bank_account_number'] = ['required'];
            $rules['newData.confirm_bank_account_number'] = ['required', 'same:newData.bank_account_number'];
        }

        return $rules;
    }

    protected function validationAttributes()
    {
        return [
            'newData.beneficiary_name' => 'beneficiary name',
            'newData.dob' => 'date of birth',
            'newData.age' => 'age',
            'newData.mobile_no' => 'mobile number',
            'newData.bank_ifsc' => 'bank IFSC',
            'newData.bank_name' => 'bank name',
            'newData.bank_branch_name' => 'bank branch',
            'newData.bank_account_number' => 'bank account number',
            'newData.confirm_bank_account_number' => 'confirm account number',
        ];
    }

    protected function resolveModule()
    {
        abort_if(blank($this->moduleCode), 404, 'Workflow module code is required.');
        abort_if(!$this->currentRoleId, 403, 'Unable to resolve current user role for workflow access.');

        $module = DynamicWorkflowModule::query()
            ->whereRaw('UPPER(module_code) = ?', [strtoupper($this->moduleCode)])
            ->where('is_active', true)
            ->first();

        abort_if(!$module, 404, 'Workflow module not found.');

        $canAccess = WorkflowsteproleMapping::where('module_id', $module->id)
            ->when($this->currentRoleId, fn($query) => $query->where('role_id', $this->currentRoleId))
            ->exists();

        abort_if(!$canAccess, 403, 'You are not allowed to access this workflow module.');

        $this->moduleId = $module->id;
        $this->moduleCode = $module->module_code;
        $this->moduleName = $module->module_name;
        $this->moduleSchemeId = $module->scheme_id;
        $this->fieldOptions = $this->getAllowedFieldOptions($module);
    }

    protected function resolveCurrentRoleId()
    {
        $lgdSession = session('lgd_session');

        if (!empty($lgdSession['role_id'])) {
            try {
                return (int) Crypt::decryptString($lgdSession['role_id']);
            } catch (\Throwable $e) {
            }
        }
    }

    protected function getAllowedFieldOptions(DynamicWorkflowModule $module)
    {
        $allowedFields = collect($module->allowed_fields ?? [])
            ->map(function ($field) {
                if (is_array($field)) {
                    return $field['field_name']
                        ?? $field['short_name']
                        ?? $field['name']
                        ?? $field['code']
                        ?? null;
                }

                return $field;
            })
            ->filter()
            ->map(fn($field) => strtolower((string) $field))
            ->values();

        if ($allowedFields->isEmpty()) {
            return $this->baseFieldOptions;
        }

        $mappedOptions = collect();

        if ($allowedFields->contains(fn($field) => in_array($field, ['beneficiary_name', 'ben_name', 'name'], true))) {
            $mappedOptions->put('beneficiary_name', $this->baseFieldOptions['beneficiary_name']);
        }

        if ($allowedFields->contains(fn($field) => in_array($field, ['dob', 'age'], true))) {
            $mappedOptions->put('dob_age', $this->baseFieldOptions['dob_age']);
        }

        if ($allowedFields->contains(fn($field) => in_array($field, ['mobile_no', 'mobile_number', 'mobile'], true))) {
            $mappedOptions->put('mobile_no', $this->baseFieldOptions['mobile_no']);
        }

        if ($allowedFields->contains(fn($field) => in_array($field, ['bank_ifsc', 'ifsc', 'bank_account_number', 'bankaccountnumber', 'bank_name', 'bank_branch_name', 'branch_name'], true))) {
            $mappedOptions->put('bank_details', $this->baseFieldOptions['bank_details']);
        }

        return $mappedOptions->isNotEmpty()
            ? $mappedOptions->toArray()
            : $this->baseFieldOptions;
    }

    protected function hydrateBeneficiaryData()
    {
        $bank = $this->beneficiary->bank;
        $ifscMaster = null;

        if (!blank(optional($bank)->ifscode)) {
            $ifscMaster = Ifsccodemaster::with('bankmaster')
                ->where('code', $bank->ifscode)
                ->first();
        }
        $dob = blank($this->beneficiary->dob)
            ? null
            : Carbon::parse($this->beneficiary->dob)->format('Y-m-d');
        $this->oldData = [
            'beneficiary_name' => $this->beneficiary->beneficiary_name ?? '',
            'dob' => $dob,
            'age' => $this->beneficiary->age,
            'mobile_no' => data_get($this->beneficiary->other_details, 'mobile_no', ''),
            'bank_ifsc' => optional($bank)->ifscode ?? '',
            'bank_name' => optional($bank)->bankname ?: optional(optional($ifscMaster)->bankmaster)->name,
            'bank_branch_name' => optional($bank)->bank_branch_name ?: optional($ifscMaster)->branch,
            'bank_account_number' => optional($bank)->bankaccountnumber ?? '',
        ];
        $this->newData = $this->oldData;
        $this->newData['confirm_bank_account_number'] = '';
    }

    protected function prepareWorkflowPayload()
    {
        $blockFieldMap = [
            'beneficiary_name' => ['beneficiary_name'],
            'dob_age'          => ['dob', 'age'],
            'mobile_no'        => ['mobile_no'],
            'bank_details'     => ['bank_ifsc', 'bank_name', 'bank_branch_name', 'bank_account_number'],
        ];

        $old = [];
        $new = [];
        $actualChangedBlocks = [];

        foreach ($this->selectedFields as $selectedField) {
            $blockHasChange = false;

            foreach ($blockFieldMap[$selectedField] ?? [] as $fieldKey) {
                $oldValue = trim((string)Arr::get($this->oldData, $fieldKey));
                $newValue = trim((string)Arr::get($this->newData, $fieldKey));

                if ($oldValue !== $newValue) {
                    $old[$fieldKey] = $oldValue;
                    $new[$fieldKey] = $newValue;
                    $blockHasChange = true;
                }
            }

            // Only add to this list if at least one sub-field in the block changed
            if ($blockHasChange) {
                $actualChangedBlocks[] = $selectedField;
            }
        }

        return [
            'old' => $old,
            'new' => $new,
            'changed_fields' => array_values($this->selectedFields),
            'actual_changed_blocks' => $actualChangedBlocks,
        ];
    }

    public function render()
    {
        return view('livewire.dynamic-workflow.request-update-beneficiary', [
            'schemes' => Scheme::where('is_active', true)->get()
        ]);
    }
}
