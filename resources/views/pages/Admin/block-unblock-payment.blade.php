<?php
use function Laravel\Folio\{name, middleware};

name('admin.block-unblock-payment');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-medium text-gray-800 leading-tight tracking-wide">
                {{ __('Block-Unblock Payment') }}
            </h2>
        </div>
    </x-slot>

    @volt
    <div class="py-6 bg-gray-50 min-h-screen">
        <?php
            use function Livewire\Volt\{state, with, updated, usesFileUploads};
            usesFileUploads();
            use App\Models\Scheme;
            use App\Models\District;
            use App\Models\Subdivision;
            use App\Models\Block;
            use App\Models\Municipality;
            use App\Models\Panchayat;
            use App\Models\FinancialYear;
            use App\Models\Month;
            use App\Models\FinancialYearMonthLot;
            use App\Models\BenPaymentDetail;
            use App\Models\Codemaster;

            state([
                'active_tab' => 'scheme', // 'scheme', 'area' or 'beneficiary'
                'scheme_id' => '',
                'district_id' => '',
                'area_type' => '',
                'subdivision_id' => '',
                'block_id' => '',
                'beneficiary_id' => '',
                'financial_year' => '',
                'month' => '',
                
                'show_results' => false,
                'result_level' => 'scheme',
                'selectedCauses' => [],
                'causeErrors' => [],
                'supporting_document' => null,
                'pendingLotChanges' => [],
                'pendingBenChanges' => [],
            ]);

            updated([
                'scheme_id' => function () {
                    $this->show_results = false;
                    $this->pendingLotChanges = [];
                    $this->pendingBenChanges = [];
                },
                'district_id' => function () {
                    $this->area_type = '';
                    $this->subdivision_id = '';
                    $this->block_id = '';
                    $this->show_results = false;
                    $this->pendingLotChanges = [];
                    $this->pendingBenChanges = [];
                },
                'area_type' => function () {
                    $this->subdivision_id = '';
                    $this->block_id = '';
                    $this->show_results = false;
                    $this->pendingLotChanges = [];
                    $this->pendingBenChanges = [];
                },
                'subdivision_id' => function () {
                    $this->show_results = false;
                    $this->pendingLotChanges = [];
                    $this->pendingBenChanges = [];
                },
                'block_id' => function () {
                    $this->show_results = false;
                    $this->pendingLotChanges = [];
                    $this->pendingBenChanges = [];
                },
                'beneficiary_id' => function () {
                    $this->show_results = false;
                    $this->pendingLotChanges = [];
                    $this->pendingBenChanges = [];
                },
                'active_tab' => function () {
                    $this->show_results = false;
                    $this->pendingLotChanges = [];
                    $this->pendingBenChanges = [];
                }
            ]);

            with(function () {
                $years = FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                
                // Set default financial year to the latest one if not set
                if (empty($this->financial_year) && !empty($years)) {
                    $this->financial_year = array_key_last($years);
                }
                
                $dynamicSubdivisions = [];
                $dynamicBlocks = [];
                $allAreas = [];
                
                if (!empty($this->district_id)) {
                    $dynamicSubdivisions = Subdivision::with('lotControl')->where('district_id', $this->district_id)
                        ->orderBy('name')
                        ->get();
                        
                    $dynamicBlocks = Block::with('lotControl')->where('district_id', $this->district_id)
                        ->orderBy('name')
                        ->get();
                        
                    foreach($dynamicSubdivisions as $sub) {
                        $allAreas[] = ['type' => 'subdivision', 'name' => $sub->name, 'id' => $sub->id, 'allow_regular_lot' => $sub->lotControl->allow_regular_lot ?? true, 'allow_arrear_lot' => $sub->lotControl->allow_arrear_lot ?? true];
                    }
                    foreach($dynamicBlocks as $block) {
                        $allAreas[] = ['type' => 'block', 'name' => $block->name, 'id' => $block->id, 'allow_regular_lot' => $block->lotControl->allow_regular_lot ?? true, 'allow_arrear_lot' => $block->lotControl->allow_arrear_lot ?? true];
                    }
                    
                    usort($allAreas, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                }
                
                $dynamicMunicipalities = [];
                if ($this->area_type === 'sub_division' && !empty($this->subdivision_id)) {
                    $dynamicMunicipalities = Municipality::with('lotControl')->where('subdivision_id', $this->subdivision_id)
                        ->orderBy('name')
                        ->get();
                }
                
                $dynamicPanchayats = [];
                if ($this->area_type === 'block' && !empty($this->block_id)) {
                    $dynamicPanchayats = Panchayat::with('lotControl')->where('block_id', $this->block_id)
                        ->orderBy('name')
                        ->get();
                }

                $dynamicBeneficiaries = [];
                if ($this->show_results && $this->active_tab === 'beneficiary' && !empty($this->scheme_id) && !empty($this->beneficiary_id)) {
                    $query = BenPaymentDetail::query();
                    $query->where('scheme_id', $this->scheme_id);
                    $query->where('ben_id', $this->beneficiary_id);
                    
                    if (!empty($this->district_id)) {
                        $query->where('created_by_dist_code', $this->district_id);
                    }
                    if ($this->area_type === 'block' && !empty($this->block_id)) {
                        $query->where('created_by_block_code', $this->block_id);
                    }
                    if ($this->area_type === 'sub_division' && !empty($this->subdivision_id)) {
                        $query->where('created_by_sdo_code', $this->subdivision_id);
                    }
                    
                    $results = $query->limit(50)->get();
                    
                    foreach ($results as $ben) {
                        $dynamicBeneficiaries[] = [
                            'id' => $ben->ben_id,
                            'name' => $ben->ben_name,
                            'status' => $ben->is_eligible ? 'Unblock' : 'Block',
                            'reason_code' => $ben->non_eligible_reason,
                        ];
                        if (!$ben->is_eligible && !isset($this->selectedCauses[$ben->ben_id])) {
                            $this->selectedCauses[$ben->ben_id] = $ben->non_eligible_reason;
                        }
                    }
                }

                return [
                    'schemes' => Scheme::with('lotControl')->where('is_active', true)->get(),
                    'districts' => District::with('lotControl')->where('is_active', true)->orderBy('name')->get(),
                    'financialYears' => $years,
                    'months' => Month::where('is_active', true)->orderBy('display_order')->pluck('name')->toArray(),
                    'areaTypes' => [
                        'sub_division' => 'Sub-Division (For ULB)',
                        'block' => 'Block (For Rural)',
                    ],
                    'subdivisions' => $dynamicSubdivisions,
                    'blocks' => $dynamicBlocks,
                    'allAreas' => $allAreas,
                    'municipalities' => $dynamicMunicipalities,
                    'panchayats' => $dynamicPanchayats,
                    'beneficiaries' => $dynamicBeneficiaries ?? [],
                    'notEligibleCauses' => Codemaster::where('parent_short_code', 'not_eligible_cause')->get(),
                ];
            });

            $search = function () {
                if ($this->active_tab === 'area') {
                    $this->validate([
                        'scheme_id' => 'required',
                    ], [
                        'scheme_id.required' => 'Scheme selection is mandatory for Area Level search.'
                    ]);
                    
                    if (empty($this->district_id)) {
                        $this->result_level = 'district';
                    } elseif (empty($this->area_type)) {
                        $this->result_level = 'all_areas';
                    } elseif ($this->area_type === 'sub_division') {
                        if (empty($this->subdivision_id)) {
                            $this->result_level = 'subdivision';
                        } else {
                            $this->result_level = 'area';
                        }
                    } elseif ($this->area_type === 'block') {
                        if (empty($this->block_id)) {
                            $this->result_level = 'block';
                        } else {
                            $this->result_level = 'panchayat';
                        }
                    } else {
                        $this->result_level = 'area';
                    }
                } elseif ($this->active_tab === 'beneficiary') {
                    $this->validate([
                        'scheme_id' => 'required',
                        'beneficiary_id' => 'required',
                    ], [
                        'scheme_id.required' => 'Scheme selection is mandatory for Beneficiary Level search.',
                        'beneficiary_id.required' => 'Beneficiary ID is mandatory.'
                    ]);
                    
                    $this->result_level = 'beneficiary';
                } else {
                    $this->result_level = 'scheme';
                }

                $this->show_results = true;
                $this->pendingLotChanges = [];
                $this->pendingBenChanges = [];
            };

            $queueLotChange = function ($level, $id, $type) {
                $key = "{$level}_{$id}_{$type}";
                if (isset($this->pendingLotChanges[$key])) {
                    unset($this->pendingLotChanges[$key]);
                } else {
                    $this->pendingLotChanges[$key] = true;
                }
            };

            $queueBenChange = function ($id, $action) {
                if (empty($this->scheme_id)) return;
                
                $ben = BenPaymentDetail::where('ben_id', $id)->where('scheme_id', $this->scheme_id)->first();
                if (!$ben) return;
                
                $originalStatus = $ben->is_eligible ? 'unblock' : 'block';
                
                if ($action === 'block') {
                    $cause = $this->selectedCauses[$id] ?? null;
                    if (empty($cause)) {
                        $this->causeErrors[$id] = 'Select cause is mandatory to block.';
                        return;
                    }
                    unset($this->causeErrors[$id]);
                } else {
                    unset($this->causeErrors[$id]);
                }
                
                if ($originalStatus === $action) {
                    unset($this->pendingBenChanges[$id]);
                } else {
                    $this->pendingBenChanges[$id] = [
                        'action' => $action,
                        'cause' => $action === 'block' ? $cause : null
                    ];
                }
            };

            $submitChanges = function () {
                if (empty($this->pendingLotChanges) && empty($this->pendingBenChanges)) {
                    return;
                }

                if (!$this->supporting_document && $this->result_level !== 'beneficiary' && !empty($this->pendingLotChanges)) {
                    $this->addError('supporting_document', 'Supporting document is required to save changes.');
                    return;
                }
                
                $path = null;
                $fileContentBase64 = null;
                $fileExtension = null;
                $fileMimeType = null;
                $codemasterDoc = \App\Models\Codemaster::where('code', '1633')->first();
                $docTypeCode = $codemasterDoc ? $codemasterDoc->code : 1633;
                $docTypeName = $codemasterDoc ? $codemasterDoc->name : 'Supporting Document for Payment Enable or Disable';

                if ($this->supporting_document && !empty($this->pendingLotChanges)) {
                    $fileContentBase64 = base64_encode(file_get_contents($this->supporting_document->getRealPath()));
                    $fileExtension = $this->supporting_document->getClientOriginalExtension();
                    $fileMimeType = $this->supporting_document->getMimeType();
                    
                    $path = $this->supporting_document->store('supporting_documents', 'public');
                }

                foreach ($this->pendingLotChanges as $key => $value) {
                    [$level, $id, $type] = explode('_', $key);
                    
                    if ($level === 'scheme') {
                        $model = Scheme::find($id);
                    } else {
                        $modelClass = match($level) {
                            'district' => District::class,
                            'subdivision' => Subdivision::class,
                            'block' => Block::class,
                            'municipality' => Municipality::class,
                            'panchayat' => Panchayat::class,
                            default => null
                        };
                        if (!$modelClass) continue;
                        $model = $modelClass::find($id);
                    }
                    
                    if (!$model) continue;
                    
                    $lotControl = $model->lotControl()->first() ?? new \App\Models\LotControl(['allow_regular_lot' => true, 'allow_arrear_lot' => true]);
                    
                    if ($type === 'regular') {
                        $lotControl->allow_regular_lot = !$lotControl->allow_regular_lot;
                    } elseif ($type === 'arrear') {
                        $lotControl->allow_arrear_lot = !$lotControl->allow_arrear_lot;
                    }
                    
                    if ($path) {
                        $lotControl->supporting_document = $path;
                        
                        $schemeIdForDoc = ($level === 'scheme') ? $id : ($this->scheme_id ?: 0);
                        $distCodeForDoc = !empty($this->district_id) ? $this->district_id : 303; // 303 is default partition

                        \App\Models\BenAttachDocument::updateOrCreate(
                            [
                                'beneficiary_id' => $id,
                                'document_type' => $docTypeCode,
                                'created_by_dist_code' => $distCodeForDoc,
                            ],
                            [
                                'scheme_id' => $schemeIdForDoc,
                                'attched_document' => $fileContentBase64,
                                'created_by' => auth()->id() ?? 1,
                                'ip_address' => request()->ip(),
                                'document_extension' => $fileExtension,
                                'document_mime_type' => $fileMimeType,
                                'doc_type_name' => $docTypeName,
                            ]
                        );
                    }
                    
                    $isBlocking = ($type === 'regular' && !$lotControl->allow_regular_lot) || ($type === 'arrear' && !$lotControl->allow_arrear_lot);
                    if ($isBlocking) {
                        $lotControl->last_block_by = auth()->id() ?? 1;
                        $lotControl->last_block_at = now();
                        $lotControl->last_block_ip = request()->ip();
                    } else {
                        $lotControl->last_unblock_by = auth()->id() ?? 1;
                        $lotControl->last_unblock_at = now();
                        $lotControl->last_unblock_ip = request()->ip();
                    }
                    
                    $model->lotControl()->save($lotControl);
                }

                foreach ($this->pendingBenChanges as $id => $change) {
                    if ($change['action'] === 'block') {
                        BenPaymentDetail::where('ben_id', $id)
                            ->where('scheme_id', $this->scheme_id)
                            ->update([
                                'is_eligible' => false,
                                'non_eligible_reason' => $change['cause']
                            ]);
                    } else {
                        BenPaymentDetail::where('ben_id', $id)
                            ->where('scheme_id', $this->scheme_id)
                            ->update([
                                'is_eligible' => true,
                                'non_eligible_reason' => null
                            ]);
                    }
                }

                $this->pendingLotChanges = [];
                $this->pendingBenChanges = [];
                $this->supporting_document = null;
                
                session()->flash('success', 'Changes submitted successfully.');
                $this->search();
            };
            
            $updateCause = function ($id, $cause_code) {
                if (empty($this->scheme_id)) return;
                
                $this->selectedCauses[$id] = $cause_code;
                unset($this->causeErrors[$id]);
                
                $ben = BenPaymentDetail::where('ben_id', $id)->where('scheme_id', $this->scheme_id)->first();
                if ($ben && !$ben->is_eligible) {
                    BenPaymentDetail::where('ben_id', $id)
                        ->where('scheme_id', $this->scheme_id)
                        ->update(['non_eligible_reason' => $cause_code ?: null]);
                }
            };
        ?>
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Flash Message -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 flex items-center justify-between">
                    <div>
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <!-- Filter Criteria Panel -->
            <div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-indigo-100/50 border border-indigo-50 rounded-2xl overflow-hidden transition-all duration-300">
                <div class="px-8 py-5 border-b border-indigo-50/60 bg-gradient-to-r from-slate-50 to-indigo-50/30 flex justify-center sm:justify-start">
                    <div class="inline-flex bg-slate-200/50 backdrop-blur-md p-1.5 rounded-xl shadow-inner border border-white/40">
                        <button wire:click="$set('active_tab', 'scheme')" class="px-6 py-2.5 text-sm font-bold tracking-wide rounded-lg transition-all duration-300 ease-out {{ $active_tab === 'scheme' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-900/5 transform scale-[1.02]' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40' }}">
                            Scheme Level
                        </button>
                        <button wire:click="$set('active_tab', 'area')" class="px-6 py-2.5 text-sm font-bold tracking-wide rounded-lg transition-all duration-300 ease-out {{ $active_tab === 'area' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-900/5 transform scale-[1.02]' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40' }}">
                            Area Level
                        </button>
                        <button wire:click="$set('active_tab', 'beneficiary')" class="px-6 py-2.5 text-sm font-bold tracking-wide rounded-lg transition-all duration-300 ease-out {{ $active_tab === 'beneficiary' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-900/5 transform scale-[1.02]' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40' }}">
                            Beneficiary Level
                        </button>
                    </div>
                </div>
                
                <div class="p-8">
                    @if($active_tab === 'scheme')
                        <div class="text-sm font-medium text-indigo-900/80 mb-4 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100/50 shadow-sm">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Click <strong class="mx-1 text-indigo-700">Search</strong> to view and toggle the regular and arrear lots for all active schemes globally.
                            </span>
                        </div>
                    @elseif($active_tab === 'area')
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <!-- Scheme -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Scheme <span class="text-red-500">*</span></label>
                                <select wire:model.live="scheme_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---Select Scheme---</option>
                                    @foreach($schemes as $sch)
                                        <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                                    @endforeach
                                </select>
                                @error('scheme_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- District -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">District</label>
                                <select wire:model.live="district_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---All Districts---</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Area Type -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Area Type</label>
                                <select wire:model.live="area_type" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                    <option value="">---All Areas---</option>
                                    @foreach($areaTypes as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Subdivision / Block -->
                            @if($area_type === 'block')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Block</label>
                                    <select wire:model.live="block_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                        <option value="">---All Blocks---</option>
                                        @foreach($blocks as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($area_type === 'sub_division')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Subdivision</label>
                                    <select wire:model.live="subdivision_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                        <option value="">---All Subdivisions---</option>
                                        @foreach($subdivisions as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Subdivision / Block</label>
                                    <select disabled class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700 bg-gray-50 opacity-70">
                                        <option value="">---Select Area Type First---</option>
                                    </select>
                                </div>
                            @endif
                        </div>
                    @elseif($active_tab === 'beneficiary')
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <!-- Scheme -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Scheme <span class="text-red-500">*</span></label>
                                <select wire:model.live="scheme_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---Select Scheme---</option>
                                    @foreach($schemes as $sch)
                                        <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                                    @endforeach
                                </select>
                                @error('scheme_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- District -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">District (Optional)</label>
                                <select wire:model.live="district_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---All Districts---</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Area Type -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Area Type (Optional)</label>
                                <select wire:model.live="area_type" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                    <option value="">---All Areas---</option>
                                    @foreach($areaTypes as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Subdivision / Block -->
                            @if($area_type === 'block')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Block (Optional)</label>
                                    <select wire:model.live="block_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                        <option value="">---All Blocks---</option>
                                        @foreach($blocks as $block)
                                            <option value="{{ $block->id }}">{{ $block->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($area_type === 'sub_division')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Subdivision (Optional)</label>
                                    <select wire:model.live="subdivision_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                        <option value="">---All Subdivisions---</option>
                                        @foreach($subdivisions as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Subdivision / Block (Optional)</label>
                                    <select disabled class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700 bg-gray-50 opacity-70">
                                        <option value="">---Select Area Type First---</option>
                                    </select>
                                </div>
                            @endif

                            <!-- Beneficiary ID -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Beneficiary ID <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.500ms="beneficiary_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" placeholder="Enter Beneficiary ID">
                                @error('beneficiary_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                    </div>

                    <!-- Search Button -->
                    <div class="mt-8 flex justify-center">
                        <button wire:click="search" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-lg hover:shadow-indigo-500/30 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 font-bold text-sm tracking-wide flex items-center transition-all duration-300 transform hover:-translate-y-1">
                            <svg class="w-5 h-5 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Explore Results
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Panel -->
            @if($show_results)
            <div class="bg-white shadow-sm border border-gray-200 rounded overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold italic text-gray-800">
                        {{ $result_level === 'scheme' ? 'List of Schemes' : ($result_level === 'district' ? 'List of Districts' : ($result_level === 'all_areas' ? 'List of Areas' : ($result_level === 'subdivision' ? 'List of Subdivisions' : ($result_level === 'block' ? 'List of Blocks' : ($result_level === 'panchayat' ? 'List of Gram Panchayats' : ($result_level === 'area' ? 'List of Municipality Names' : 'Beneficiary Details')))))) }}
                    </h3>
                </div>
                
                @if($result_level !== 'beneficiary')
                <div class="px-4 py-4 bg-yellow-50 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-800">Supporting Document</h4>
                        <p class="text-xs text-gray-600 mt-1">You must upload a supporting document to unlock the lot toggles.</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <input type="file" wire:model="supporting_document" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        @error('supporting_document') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        @if($supporting_document)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Unlocked
                            </span>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 w-16">
                                        SL No
                                    </th>
                                    @if($result_level === 'scheme')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Scheme Name
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'district')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            District
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'subdivision')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Subdivision
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'block')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Block
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'all_areas')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Area Name
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Area Type
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'area')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Municipality
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'panchayat')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Gram Panchayat
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @else
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Beneficiary ID
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Beneficiary Name
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Select Cause
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Current Status
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Action
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @if($result_level === 'scheme')
                                    @foreach($schemes as $index => $sch)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $sch->display_name ?? $sch->name }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('scheme', {{ $sch->id }}, 'regular')" {{ (isset($pendingLotChanges["scheme_{$sch->id}_regular"]) ? !($sch->lotControl->allow_regular_lot ?? true) : ($sch->lotControl->allow_regular_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('scheme', {{ $sch->id }}, 'arrear')" {{ (isset($pendingLotChanges["scheme_{$sch->id}_arrear"]) ? !($sch->lotControl->allow_arrear_lot ?? true) : ($sch->lotControl->allow_arrear_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'district')
                                    @foreach($districts as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row->name }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('district', {{ $row->id }}, 'regular')" {{ (isset($pendingLotChanges["district_{$row->id}_regular"]) ? !($row->lotControl->allow_regular_lot ?? true) : ($row->lotControl->allow_regular_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('district', {{ $row->id }}, 'arrear')" {{ (isset($pendingLotChanges["district_{$row->id}_arrear"]) ? !($row->lotControl->allow_arrear_lot ?? true) : ($row->lotControl->allow_arrear_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'subdivision')
                                    @foreach($subdivisions as $index => $sub)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $label }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('subdivision', {{ $sub->id }}, 'regular')" {{ (isset($pendingLotChanges["subdivision_{$sub->id}_regular"]) ? !($sub->lotControl->allow_regular_lot ?? true) : ($sub->lotControl->allow_regular_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('subdivision', {{ $sub->id }}, 'arrear')" {{ (isset($pendingLotChanges["subdivision_{$sub->id}_arrear"]) ? !($sub->lotControl->allow_arrear_lot ?? true) : ($sub->lotControl->allow_arrear_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'block')
                                    @foreach($blocks as $index => $block)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $label }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('block', {{ $block->id }}, 'regular')" {{ (isset($pendingLotChanges["block_{$block->id}_regular"]) ? !($block->lotControl->allow_regular_lot ?? true) : ($block->lotControl->allow_regular_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('block', {{ $block->id }}, 'arrear')" {{ (isset($pendingLotChanges["block_{$block->id}_arrear"]) ? !($block->lotControl->allow_arrear_lot ?? true) : ($block->lotControl->allow_arrear_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'all_areas')
                                    @foreach($allAreas as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row['name'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row['type'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    @php $rowType = strtolower($row['type']); @endphp
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('{{ $rowType }}', {{ $row['id'] }}, 'regular')" {{ (isset($pendingLotChanges["{$rowType}_{$row['id']}_regular"]) ? !$row['allow_regular_lot'] : $row['allow_regular_lot']) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('{{ $rowType }}', {{ $row['id'] }}, 'arrear')" {{ (isset($pendingLotChanges["{$rowType}_{$row['id']}_arrear"]) ? !$row['allow_arrear_lot'] : $row['allow_arrear_lot']) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'panchayat')
                                    @foreach($panchayats as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row->name }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('panchayat', {{ $row->id }}, 'regular')" {{ (isset($pendingLotChanges["panchayat_{$row->id}_regular"]) ? !($row->lotControl->allow_regular_lot ?? true) : ($row->lotControl->allow_regular_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('panchayat', {{ $row->id }}, 'arrear')" {{ (isset($pendingLotChanges["panchayat_{$row->id}_arrear"]) ? !($row->lotControl->allow_arrear_lot ?? true) : ($row->lotControl->allow_arrear_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'area')
                                    @foreach($municipalities as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row->name }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('municipality', {{ $row->id }}, 'regular')" {{ (isset($pendingLotChanges["municipality_{$row->id}_regular"]) ? !($row->lotControl->allow_regular_lot ?? true) : ($row->lotControl->allow_regular_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="queueLotChange('municipality', {{ $row->id }}, 'arrear')" {{ (isset($pendingLotChanges["municipality_{$row->id}_arrear"]) ? !($row->lotControl->allow_arrear_lot ?? true) : ($row->lotControl->allow_arrear_lot ?? true)) ? 'checked' : '' }} {{ $supporting_document ? '' : 'disabled' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium {{ $supporting_document ? '' : 'opacity-50' }}">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    @foreach($beneficiaries as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row['id'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $row['name'] }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <select wire:change="updateCause({{ $row['id'] }}, $event.target.value)" class="form-select h-8 py-1 pl-2 pr-8 text-xs border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 max-w-[180px]">
                                                    <option value="">-- Select Cause --</option>
                                                    @foreach($notEligibleCauses as $cause)
                                                        <option value="{{ $cause->code }}" {{ ($selectedCauses[$row['id']] ?? '') == $cause->code ? 'selected' : '' }}>{{ $cause->name }}</option>
                                                    @endforeach
                                                </select>
                                                @if(isset($causeErrors[$row['id']]))
                                                    <div class="text-red-500 text-xs mt-1">{{ $causeErrors[$row['id']] }}</div>
                                                @endif
                                            </td>
                                            @php
                                                $currentStatus = $row['status'];
                                                if (isset($pendingBenChanges[$row['id']])) {
                                                    $currentStatus = $pendingBenChanges[$row['id']]['action'] === 'block' ? 'Block' : 'Unblock';
                                                }
                                            @endphp
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $currentStatus === 'Unblock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $currentStatus === 'Unblock' ? 'Unblocked' : 'Blocked' }}
                                                    @if(isset($pendingBenChanges[$row['id']]))
                                                        <span class="ml-1 text-[10px] opacity-70">(Pending)</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @if($currentStatus === 'Unblock')
                                                    <button wire:click="queueBenChange({{ $row['id'] }}, 'block')" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        Block
                                                    </button>
                                                @else
                                                    <button wire:click="queueBenChange({{ $row['id'] }}, 'unblock')" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        Unblock
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if(count($pendingLotChanges) > 0 || count($pendingBenChanges) > 0)
                <div class="px-4 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button wire:click="submitChanges" class="px-6 py-2.5 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 font-bold text-sm tracking-wide transition-all duration-300">
                        Submit Changes
                    </button>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
    @endvolt
</x-app-layout>
