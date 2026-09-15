<?php
use function Laravel\Folio\{name, middleware};

name('admin.payment-lot-transaction');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-orange-500 leading-tight">
                {{ __('Payment Lot Transaction') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Manage and execute integration steps for generated payment lots.</p>
        </div>
    </x-slot>

    @volt
    <div class="py-8 bg-[#fdfaf5] min-h-screen">
        <?php
            use function Livewire\Volt\{state, with, updated};
            use App\Models\Scheme;
            use App\Models\Codemaster;
            use App\Models\FinancialYear;
            use App\Models\Month;
            use App\Models\PaymentLotMaster;

            state([
                'scheme_id' => '',
                'lot_financial_year' => '',
                'lot_month' => '',
                'payment_type' => '',
                'lot_type' => '',
                'target_payment_mode' => '',
                'has_searched' => false,
                'show_bill_modal' => false,
                'current_bill_lot_no' => null,
                'bill_data' => [
                    'treasury_code' => '',
                    'ddo_code' => '',
                    'head_of_account' => '',
                    'gross_amount' => '',
                    'net_amount' => '',
                    'sanction_amount' => '',
                    'sanction_number' => '',
                    'sanction_date' => '',
                    'issuing_authority' => '',
                    'bill_number' => '',
                    'bill_date' => '',
                ],
            ]);

            with(function () {
                $schemes = Scheme::where('is_active', true)->get();
                $financialYears = FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                $months = Month::where('is_active', true)->orderBy('display_order')->pluck('name', 'code')->toArray();
                $paymentTypes = Codemaster::where('parent_short_code', 'payment_type')->where('is_active', true)->pluck('name', 'code')->toArray();
                $lotTypes = Codemaster::where('parent_short_code', 'lot_type')->where('is_active', true)->pluck('name', 'code')->toArray();
                $paymentModes = Codemaster::where('parent_short_code', 'payment_mode')->where('is_active', true)->pluck('name', 'code')->toArray();

                if ($this->has_searched) {
                    $query = PaymentLotMaster::query();
                    
                    if ($this->scheme_id) {
                        $query->where('scheme_id', $this->scheme_id);
                    }
                    if ($this->lot_financial_year) {
                        $query->where('lot_year', $this->lot_financial_year);
                    }
                    if ($this->lot_month) {
                        $query->where('lot_month', $this->lot_month);
                    }
                    if ($this->lot_type) {
                        $query->where('lot_type_id', $this->lot_type);
                    }
                    if ($this->target_payment_mode) {
                        $query->where('payment_mode', $this->target_payment_mode);
                    }

                    $lots = $query->orderBy('created_at', 'desc')->get();
                } else {
                    $lots = collect();
                }
                
                $allCodeMap = Codemaster::pluck('name', 'code')->toArray();

                return compact('schemes', 'financialYears', 'months', 'paymentTypes', 'lotTypes', 'paymentModes', 'lots', 'allCodeMap');
            });

            $resetFilters = function () {
                $this->reset(['scheme_id', 'lot_financial_year', 'lot_month', 'payment_type', 'lot_type', 'target_payment_mode', 'has_searched']);
            };

            $search = function () {
                $this->has_searched = true;
            };

            $downloadExcel = function ($lotNo, $type) {
                session()->flash('status', "Beneficiary list download for Lot $lotNo ($type) initiated.");
            };

            $executeStep = function ($lotNo, $actionKey) {
                if ($actionKey === 'bill_generation') {
                    $lotMaster = PaymentLotMaster::where('lot_no', $lotNo)->firstOrFail();
                    $this->current_bill_lot_no = $lotNo;
                    $this->bill_data = [
                        'treasury_code' => 'TEST-TR',
                        'ddo_code' => 'TEST-DDO',
                        'head_of_account' => 'TEST-HOA',
                        'gross_amount' => $lotMaster->total_amount,
                        'net_amount' => $lotMaster->total_amount,
                        'sanction_amount' => $lotMaster->total_amount,
                        'sanction_number' => '',
                        'sanction_date' => '',
                        'issuing_authority' => '',
                        'bill_number' => '',
                        'bill_date' => '',
                    ];
                    $this->show_bill_modal = true;
                    return;
                }

                try {
                    $lotMaster = PaymentLotMaster::where('lot_no', $lotNo)->firstOrFail();
                    $service = \App\Services\Payment\PaymentGatewayFactory::make($lotMaster);
                    
                    $result = $service->executeStep($lotMaster, $actionKey);

                    if (isset($result['status'])) {
                        if ($result['status'] == 1) {
                            session()->flash('status', $result['msg'] ?? 'Action executed successfully.');
                        } else {
                            session()->flash('error', $result['msg'] ?? 'Action failed.');
                        }
                    } else {
                        session()->flash('status', 'Action executed.');
                    }
                } catch (\Exception $e) {
                    session()->flash('error', 'Error executing action for lot ' . $lotNo . ': ' . $e->getMessage());
                }
            };

            $closeBillModal = function () {
                $this->show_bill_modal = false;
                $this->current_bill_lot_no = null;
            };

            $submitBillGeneration = function () {
                $this->validate([
                    'bill_data.sanction_number' => 'required',
                    'bill_data.sanction_date' => 'required',
                    'bill_data.issuing_authority' => 'required',
                    'bill_data.bill_number' => 'required',
                    'bill_data.bill_date' => 'required',
                ]);

                try {
                    $lotMaster = PaymentLotMaster::where('lot_no', $this->current_bill_lot_no)->firstOrFail();
                    $service = \App\Services\Payment\PaymentGatewayFactory::make($lotMaster);
                    
                    $result = $service->executeStep($lotMaster, 'bill_generation', $this->bill_data);

                    if (isset($result['status'])) {
                        if ($result['status'] == 1) {
                            session()->flash('status', $result['msg'] ?? 'Bill generated successfully.');
                        } else {
                            session()->flash('error', $result['msg'] ?? 'Bill generation failed.');
                        }
                    } else {
                        session()->flash('status', 'Bill generated.');
                    }
                    
                    $this->closeBillModal();
                } catch (\Exception $e) {
                    session()->flash('error', 'Error executing action: ' . $e->getMessage());
                }
            };
        ?>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Filters Section -->
            <div class="relative bg-white shadow-sm border border-orange-200 rounded-lg p-6 pt-10">
                <span class="absolute -top-4 left-6 bg-orange-400 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter Lots
                </span>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-2 ml-2">
                    <!-- Scheme -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Scheme</label>
                        <select wire:model="scheme_id" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">-- All Schemes --</option>
                            @foreach($schemes as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Financial Year</label>
                        <select wire:model="lot_financial_year" class="block w-full border-gray-200 rounded-md shadow-sm text-gray-600 focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
                            <option value="">-- All Years --</option>
                            @foreach($financialYears as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Month</label>
                        <select wire:model="lot_month" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">-- All Months --</option>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Select Payment Type *</label>
                        <select wire:model="payment_type" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">-- Select Payment Type --</option>
                            @foreach($paymentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lot Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Lot Type *</label>
                        <select wire:model="lot_type" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">-- Select Lot Type --</option>
                            @foreach($lotTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Target Payment Mode -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Target Payment Mode *</label>
                        <select wire:model="target_payment_mode" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">-- Select Mode --</option>
                            @foreach($paymentModes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end mt-4 space-x-3">
                    <button wire:click="resetFilters" class="px-6 py-2 bg-gray-500 text-white rounded shadow-sm hover:bg-gray-600 font-semibold text-sm transition-colors">
                        Reset Filters
                    </button>
                    <button wire:click="search" class="px-6 py-2 bg-orange-500 text-white rounded shadow-sm hover:bg-orange-600 font-semibold text-sm transition-colors">
                        Search
                    </button>
                </div>
            </div>

            <!-- Lots Table Section -->
            <div class="relative bg-white shadow-sm border border-orange-200 rounded-lg p-6 pt-10">
                <span class="absolute -top-4 left-6 bg-orange-500 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Transactions list
                </span>
                
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lot No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Gateway</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Beneficiaries</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Amount (₹)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($lots as $lot)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $lot->lot_no }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ strtoupper($allCodeMap[$lot->payment_mode] ?? $lot->payment_mode) }} <br>
                                        <span class="text-xs text-gray-400">Type: {{ strtoupper($lot->int_type ?? 'API') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="flex items-center space-x-2">
                                            <span>{{ number_format($lot->ben_count) }}</span>
                                            @if(($lot->ben_count ?? 0) > 0)
                                                <button wire:click="downloadExcel('{{ $lot->lot_no }}', 'total')" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1 rounded transition-colors" title="Download Beneficiaries CSV">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                                        {{ number_format($lot->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $allCodeMap[$lot->cur_status] ?? $lot->cur_status ?? 'Generated' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <!-- Unified Dynamic Actions Component -->
                                        @include('components.payment.dynamic-actions', ['lot' => $lot])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center font-medium">
                                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        @if($has_searched)
                                            No Payment Lots found matching the selected filters.
                                        @else
                                            Please apply filters and click Search to load data.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Bill Generation Modal -->
            @if($show_bill_modal)
                <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900 bg-opacity-50">
                    <div class="relative w-full max-w-4xl p-4">
                        <div class="relative bg-white rounded-lg shadow">
                            <div class="flex items-start justify-between p-4 border-b rounded-t bg-blue-600">
                                <h3 class="text-xl font-semibold text-white">
                                    Bill Generation
                                </h3>
                                <button wire:click="closeBillModal" type="button" class="text-white bg-transparent hover:bg-blue-700 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                </button>
                            </div>
                            
                            <div class="p-6 space-y-6">
                                <h4 class="text-sm font-bold text-blue-600 border-b pb-2">Bill Details (Lot No: {{ $current_bill_lot_no }})</h4>
                                
                                <div class="grid grid-cols-3 gap-6 text-center text-sm">
                                    <div>
                                        <div class="font-semibold text-gray-600">Treasury Code</div>
                                        <div class="mt-1 font-bold">{{ $bill_data['treasury_code'] }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-600">DDO Code</div>
                                        <div class="mt-1 font-bold">{{ $bill_data['ddo_code'] }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-600">Head of Account</div>
                                        <div class="mt-1 font-bold">{{ $bill_data['head_of_account'] }}</div>
                                    </div>
                                    
                                    <div class="border-t pt-4">
                                        <div class="font-semibold text-gray-600">Gross Amount (₹)</div>
                                        <div class="mt-1 font-bold">{{ number_format($bill_data['gross_amount'], 2) }}</div>
                                    </div>
                                    <div class="border-t pt-4">
                                        <div class="font-semibold text-gray-600">Net Amount (₹)</div>
                                        <div class="mt-1 font-bold">{{ number_format($bill_data['net_amount'], 2) }}</div>
                                    </div>
                                    <div class="border-t pt-4">
                                        <div class="font-semibold text-gray-600">Sanction Amount (₹)</div>
                                        <div class="mt-1 font-bold">{{ number_format($bill_data['sanction_amount'], 2) }}</div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-6 border-t pt-6">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Sanction Number *</label>
                                        <input type="text" wire:model="bill_data.sanction_number" class="w-full border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('bill_data.sanction_number') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Sanction Date *</label>
                                        <input type="date" wire:model="bill_data.sanction_date" class="w-full border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('bill_data.sanction_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Issuing Authority *</label>
                                        <input type="text" wire:model="bill_data.issuing_authority" class="w-full border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('bill_data.issuing_authority') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Bill Number *</label>
                                        <input type="text" wire:model="bill_data.bill_number" class="w-full border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('bill_data.bill_number') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Bill Date *</label>
                                        <input type="date" wire:model="bill_data.bill_date" class="w-full border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('bill_data.bill_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-center p-6 border-t border-gray-200 rounded-b space-x-4 bg-gray-50">
                                <button wire:click="submitBillGeneration" type="button" class="text-blue-700 bg-blue-100 border border-blue-200 hover:bg-blue-200 font-bold rounded text-sm px-5 py-2.5 text-center flex items-center shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    Bill Share
                                </button>
                                <button wire:click="closeBillModal" type="button" class="text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 font-semibold rounded text-sm px-5 py-2.5 text-center flex items-center shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Reset / Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endvolt
</x-app-layout>
