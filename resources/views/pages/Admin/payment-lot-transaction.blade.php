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
            <p class="text-sm text-gray-500 mt-1">Manage scheme criteria, lot type, and pending beneficiary details.</p>
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
            use App\Models\District;
            use App\Models\Subdivision;
            use App\Models\Block;
            use App\Models\Municipality;
            use App\Models\Panchayat;
            use App\Models\Ward;

            state([
                'payment_type' => '',
                'scheme_criteria' => 'lot_wise_beneficiary',
                'scheme' => '',
                'lot_type' => '',
                'lot_financial_year' => '',
                'lot_month' => '',
                'target_payment_mode' => '',
                'previewSummary' => null,
                'lots' => null,
                'breadcrumb' => null,
            ]);

            $updatedScheme = function ($value) {
                $this->lot_financial_year = '';
                $this->lot_month = '';
                $this->lot_type = '';
                $this->target_payment_mode = '';
            };

            $updatedLotFinancialYear = function ($value) {
                $this->lot_month = '';
                $this->lot_type = '';
                $this->target_payment_mode = '';
            };

            $updatedLotMonth = function ($value) {
                $this->lot_type = '';
                $this->target_payment_mode = '';
                
                if ($this->scheme && $this->lot_financial_year && $this->lot_month) {
                    $setting = \App\Models\PaymentMainSetting::where('scheme_id', $this->scheme)
                        ->where('financial_year', $this->lot_financial_year)
                        ->first();
                        
                    if ($setting) {
                        $monthField = strtolower($this->lot_month);
                        $monthData = $setting->$monthField;
                        if (is_array($monthData)) {
                            if (!empty($monthData['payment_mode'])) {
                                $this->target_payment_mode = $monthData['payment_mode'];
                            }
                            if (!empty($monthData['payment_type'])) {
                                $this->payment_type = $monthData['payment_type'];
                            }

                            if (isset($monthData['52301'])) {
                                $isRegular = $monthData['52301']['is_regular_lot'] ?? false;
                                $isArrear = $monthData['52301']['is_arrear_lot'] ?? false;

                                $allLotTypes = \App\Models\Codemaster::where('parent_short_code', 'lot_type')->where('is_active', true)->pluck('name', 'code')->toArray();
                                $validLotTypes = [];
                                foreach ($allLotTypes as $code => $name) {
                                    if (stripos($name, 'REGULAR') !== false && $isRegular) {
                                        $validLotTypes[$code] = $name;
                                    }
                                    if ((stripos($name, 'ARREAR') !== false || stripos($name, 'ARRER') !== false) && $isArrear) {
                                        $validLotTypes[$code] = $name;
                                    }
                                }
                                if (count($validLotTypes) === 1) {
                                    $this->lot_type = array_key_first($validLotTypes);
                                }
                            }
                        }
                    }
                }
            };



            with(function () {
                $allLotTypes = Codemaster::where('parent_short_code', 'lot_type')->where('is_active', true)->pluck('name', 'code')->toArray();
                $lotTypes = $allLotTypes;
                
                $allFinancialYears = FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                $financialYears = $allFinancialYears;

                if ($this->scheme) {
                    $availableYears = \App\Models\PaymentMainSetting::where('scheme_id', $this->scheme)
                        ->pluck('financial_year')
                        ->toArray();
                        
                    $financialYears = [];
                    foreach ($allFinancialYears as $code => $name) {
                        if (in_array($code, $availableYears)) {
                            $financialYears[$code] = $name;
                        }
                    }
                }

                $allMonths = Month::where('is_active', true)->orderBy('display_order')->pluck('name', 'code')->toArray();
                $months = $allMonths;

                if ($this->scheme && $this->lot_financial_year) {
                    $setting = \App\Models\PaymentMainSetting::where('scheme_id', $this->scheme)
                        ->where('financial_year', $this->lot_financial_year)
                        ->first();
                        
                    $months = [];
                    if ($setting) {
                        foreach ($allMonths as $code => $displayName) {
                            $monthField = strtolower($code);
                            $monthData = $setting->$monthField;
                            if (is_array($monthData) && isset($monthData['52301'])) {
                                if (($monthData['52301']['is_regular_lot'] ?? false) || ($monthData['52301']['is_arrear_lot'] ?? false)) {
                                    $months[$code] = $displayName;
                                }
                            }
                        }
                    }
                }

                if ($this->scheme && $this->lot_financial_year && $this->lot_month) {
                    $setting = \App\Models\PaymentMainSetting::where('scheme_id', $this->scheme)
                        ->where('financial_year', $this->lot_financial_year)
                        ->first();

                    $lotTypes = [];
                    if ($setting) {
                        $monthField = strtolower($this->lot_month);
                        $monthData = $setting->$monthField;
                        if (is_array($monthData) && isset($monthData['52301'])) {
                            $isRegular = $monthData['52301']['is_regular_lot'] ?? false;
                            $isArrear = $monthData['52301']['is_arrear_lot'] ?? false;

                            foreach ($allLotTypes as $code => $name) {
                                if (stripos($name, 'REGULAR') !== false && $isRegular) {
                                    $lotTypes[$code] = $name;
                                }
                                if ((stripos($name, 'ARREAR') !== false || stripos($name, 'ARRER') !== false) && $isArrear) {
                                    $lotTypes[$code] = $name;
                                }
                            }
                        }
                    }
                }
                return [
                    'schemes' => Scheme::where('is_active', true)->get(),
                    'paymentTypes' => Codemaster::where('parent_short_code', 'payment_type')->where('is_active', true)->pluck('name', 'code')->toArray(),
                    'months' => $months,
                    'financialYears' => $financialYears,
                    'targetPaymentModes' => Codemaster::where('parent_short_code', 'payment_mode')->where('is_active', true)->pluck('name', 'code')->toArray(),
                    'lotTypes' => $lotTypes,
                ];
            });

            $resetForm = function () {
                $this->reset([
                    'payment_type',
                    'scheme_criteria',
                    'scheme',
                    'lot_type',
                    'lot_financial_year',
                    'lot_month',
                    'target_payment_mode',
                    'lots',
                    'breadcrumb',
                ]);
                
                // Set default values back
                $this->scheme_criteria = 'lot_wise_beneficiary';
            };

            $search = function () {
                $this->validate([
                    'scheme' => 'required',
                    'lot_financial_year' => 'required',
                    'lot_month' => 'required',
                    'payment_type' => 'required',
                    'lot_type' => 'required',
                    'target_payment_mode' => 'required',
                ], [
                    'scheme.required' => 'Please select a scheme.',
                    'lot_financial_year.required' => 'Please select a financial year.',
                    'lot_month.required' => 'Please select a lot month.',
                    'payment_type.required' => 'Please select a payment type.',
                    'lot_type.required' => 'Please select a lot type.',
                    'target_payment_mode.required' => 'Please select a target payment mode.',
                ]);

                $query = \App\Models\PaymentLotMaster::query();

                $query->where('scheme_id', $this->scheme)
                      ->where('lot_year', $this->lot_financial_year)
                      ->where('lot_month', $this->lot_month)
                      ->where('lot_type_id', $this->lot_type)
                      ->where('payment_mode', $this->target_payment_mode);

                $schemeName = \App\Models\Scheme::find($this->scheme)?->name;
                $monthName = \App\Models\Month::where('code', $this->lot_month)->first()?->name ?? $this->lot_month;
                $paymentTypeName = \App\Models\Codemaster::where('code', $this->payment_type)->first()?->name;
                $lotTypeName = \App\Models\Codemaster::where('code', $this->lot_type)->first()?->name;
                $targetPaymentModeName = \App\Models\Codemaster::where('code', $this->target_payment_mode)->first()?->name;

                $this->breadcrumb = implode(' / ', array_filter([
                    $schemeName,
                    $this->lot_financial_year,
                    $monthName,
                    $paymentTypeName,
                    $lotTypeName,
                    $targetPaymentModeName
                ]));

                $this->lots = $query->orderBy('lot_no', 'desc')->get();
            };
        ?>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <!-- Transaction Details -->
            <div class="relative bg-white shadow-sm border border-orange-200 rounded-lg p-6 pt-10">
                <span class="absolute -top-4 left-6 bg-orange-400 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Transaction Details
                </span>
                
               

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 ml-2">
                    <!-- Scheme -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Select Scheme <span class="text-red-500">*</span></label>
                        <select wire:model="scheme" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">---Select Scheme---</option>
                            @foreach($schemes as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                            @endforeach
                        </select>
                        @error('scheme') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Lot Financial Year -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Lot Financial Year <span class="text-red-500">*</span></label>
                        <select wire:model="lot_financial_year" class="block w-full border-gray-200 rounded-md shadow-sm text-gray-600 focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
                            <option value="">Select Financial Year</option>
                            @foreach($financialYears as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('lot_financial_year') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Lot Month -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Lot Month <span class="text-red-500">*</span></label>
                        <select wire:model="lot_month" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">Select Month</option>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('lot_month') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Select Payment Type -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Select Payment Type <span class="text-red-500">*</span></label>
                        <select wire:model="payment_type" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">---Select Payment Type---</option>
                            @foreach($paymentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Lot Type -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Lot Type <span class="text-red-500">*</span></label>
                        <select wire:model="lot_type" class="block w-full border-gray-200 rounded-md shadow-sm text-gray-600 focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
                            <option value="">Select Lot Type</option>
                            @foreach($lotTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('lot_type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Target Payment Mode -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Target Payment Mode <span class="text-red-500">*</span></label>
                        <select wire:model="target_payment_mode" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">---Select Target Payment Mode---</option>
                            @foreach($targetPaymentModes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('target_payment_mode') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>




            <!-- Actions -->
            <div class="flex justify-center items-center space-x-4 pt-4 pb-8 mt-8">
                <button wire:click="resetForm" class="px-8 py-2.5 bg-gray-500 text-white rounded shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 font-semibold text-sm transition-colors">
                    Reset
                </button>

                <button wire:click="search" class="px-8 py-2.5 bg-orange-300 text-orange-900 rounded shadow-sm hover:bg-orange-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-300 font-semibold text-sm flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Search
                </button>
            </div>
            
            <!-- Results Table -->
            @if(isset($lots))
                @if($breadcrumb)
                    <div class="mb-6 bg-orange-50 border border-orange-200 rounded-lg p-4 text-sm text-orange-900 font-semibold shadow-sm flex items-center">
                        <svg class="w-5 h-5 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Search Criteria: <span class="ml-2 font-normal text-gray-700">{{ $breadcrumb }}</span></span>
                    </div>
                @endif
                
                @if(count($lots) > 0)
                    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Lot No</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Scheme</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Month / Year</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($lots as $lot)
                                    <tr class="hover:bg-orange-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $lot->lot_no }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \App\Models\Scheme::find($lot->scheme_id)?->name ?? $lot->scheme_id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $lot->lot_month }} / {{ $lot->lot_year }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-orange-600 hover:text-orange-900 bg-orange-100 px-3 py-1 rounded-md text-xs font-bold transition-colors">View Details</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-white shadow-sm border border-orange-200 rounded-lg p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-orange-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Payment Lots Found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria or lot number.</p>
                    </div>
                @endif
            @endif
            
        </div>
    </div>
    @endvolt
</x-app-layout>
