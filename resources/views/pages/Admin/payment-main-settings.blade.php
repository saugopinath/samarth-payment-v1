<?php
use function Laravel\Folio\{name, middleware};

name('admin.payment-main-settings');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-orange-500 leading-tight">
                {{ __('Payment Main Settings') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Configure payment amounts and modes for each month.</p>
        </div>
    </x-slot>

    @volt
    <div class="py-8 bg-[#fdfaf5] min-h-screen">
        <?php
            use function Livewire\Volt\{state, with};
            use App\Models\FinancialYear;
            use App\Models\Month;
            use App\Models\PaymentMainSetting;
            use Illuminate\Support\Facades\DB;

            state([
                'selected_scheme' => '',
                'selected_financial_year' => '',
                'show_months' => false,
                'settings' => [],
            ]);

            with(function () {
                $schemes = \App\Models\Scheme::where('is_active', true)->get();
                $financialYears = \App\Models\FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                
                // Fetch Payment Modes and Types directly from Codemasters
                $paymentModes = DB::table('codemasters')
                    ->where('parent_short_code', 'payment_mode')
                    ->orderBy('name')
                    ->get();
                
                $paymentTypes = DB::table('codemasters')
                    ->where('parent_short_code', 'payment_type')
                    ->orderBy('name')
                    ->get();
                
                $months = collect();
                
                if ($this->show_months) {
                    $months = \App\Models\Month::where('is_active', true)->orderBy('display_order')->get();
                }

                return [
                    'schemes' => $schemes,
                    'financialYears' => $financialYears,
                    'months' => $months,
                    'paymentModes' => $paymentModes,
                    'paymentTypes' => $paymentTypes,
                ];
            });

            $showMonthsList = function () {
                $this->validate([
                    'selected_scheme' => 'required',
                    'selected_financial_year' => 'required'
                ], [
                    'selected_scheme.required' => 'Please select a scheme.',
                    'selected_financial_year.required' => 'Please select a financial year.'
                ]);
                
                $months = \App\Models\Month::where('is_active', true)->orderBy('display_order')->get();
                $mainSetting = \App\Models\PaymentMainSetting::where('scheme_id', $this->selected_scheme)
                    ->where('financial_year', $this->selected_financial_year)
                    ->first();

                $this->settings = [];
                foreach ($months as $month) {
                    $colName = strtolower($month->code);
                    
                    if ($mainSetting && isset($mainSetting->$colName)) {
                        $monthData = $mainSetting->$colName;
                        $this->settings[$colName]['amount'] = $monthData['amount'] ?? 0;
                        $this->settings[$colName]['payment_mode'] = $monthData['payment_mode'] ?? '';
                        $this->settings[$colName]['payment_type'] = $monthData['payment_type'] ?? '';
                        
                        foreach (['52301', '52302', '52303'] as $type) {
                            $this->settings[$colName][$type]['is_regular_lot'] = $monthData[$type]['is_regular_lot'] ?? false;
                            $this->settings[$colName][$type]['is_arrear_lot'] = $monthData[$type]['is_arrear_lot'] ?? false;
                        }
                    } else {
                        $this->settings[$colName]['amount'] = 0;
                        $this->settings[$colName]['payment_mode'] = '';
                        $this->settings[$colName]['payment_type'] = '';
                        
                        foreach (['52301', '52302', '52303'] as $type) {
                            $this->settings[$colName][$type]['is_regular_lot'] = false;
                            $this->settings[$colName][$type]['is_arrear_lot'] = false;
                        }
                    }
                }

                $this->show_months = true;
            };

            $updatedSelectedScheme = function () {
                $this->show_months = false;
            };

            $updatedSelectedFinancialYear = function () {
                $this->show_months = false;
            };

            $finalSubmit = function () {
                if (empty($this->selected_scheme) || empty($this->selected_financial_year)) {
                    return;
                }
                
                $updateData = [];
                foreach ($this->settings as $colName => $data) {
                    // Ensure it is stored as JSON array
                    $monthJson = [
                        'amount' => is_numeric($data['amount']) ? (float)$data['amount'] : 0,
                        'payment_mode' => $data['payment_mode'] ?? '',
                        'payment_type' => $data['payment_type'] ?? ''
                    ];
                    
                    foreach (['52301', '52302', '52303'] as $type) {
                        if (isset($data[$type])) {
                            $monthJson[$type] = [
                                'is_regular_lot' => $data[$type]['is_regular_lot'] ?? false,
                                'is_arrear_lot' => $data[$type]['is_arrear_lot'] ?? false,
                            ];
                        }
                    }
                    
                    $updateData[$colName] = $monthJson;
                }

                \App\Models\PaymentMainSetting::updateOrCreate(
                    [
                        'scheme_id' => $this->selected_scheme,
                        'financial_year' => $this->selected_financial_year,
                    ],
                    $updateData
                );
                
                session()->flash('status', 'Payment settings saved successfully!');
            };
        ?>
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <!-- Filters Section -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-orange-100 relative">
                <div class="absolute top-0 left-0 w-2 h-full bg-orange-400 rounded-l-lg"></div>
                <div class="flex flex-col md:flex-row gap-6 items-end">
                    
                    <div class="w-full md:w-1/3 pl-4">
                        <label for="scheme" class="block text-sm font-bold text-gray-700 mb-1.5 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            Select Scheme <span class="text-red-500 ml-1">*</span>
                        </label>
                        <select wire:model.live="selected_scheme" id="scheme" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
                            <option value="">-- Select Scheme --</option>
                            @foreach($schemes as $scheme)
                                <option value="{{ $scheme->id }}">{{ $scheme->display_name ?? $scheme->name }}</option>
                            @endforeach
                        </select>
                        @error('selected_scheme') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full md:w-1/3">
                        <label for="financial_year" class="block text-sm font-bold text-gray-700 mb-1.5 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Financial Year <span class="text-red-500 ml-1">*</span>
                        </label>
                        <select wire:model.live="selected_financial_year" id="financial_year" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
                            <option value="">-- Select Financial Year --</option>
                            @foreach($financialYears as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('selected_financial_year') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full md:w-1/3 flex items-end pb-0.5">
                        <button wire:click="showMonthsList" class="w-full px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-md shadow-sm text-sm font-bold transition-colors flex justify-center items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Load Configuration
                        </button>
                    </div>
                </div>
            </div>

            @if($show_months)
            <div class="relative bg-white shadow-sm border border-blue-200 rounded-lg p-6 pt-10 animate-[fadeIn_0.3s_ease-in-out]">
                <span class="absolute -top-4 left-6 bg-blue-500 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Monthly Configuration
                </span>

                <div class="mt-4 overflow-x-auto border border-gray-300">
                    <table class="min-w-full divide-y divide-gray-300 text-sm border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-300">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 border-r border-gray-300 uppercase text-xs">
                                    Month Name
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-300 text-sm">
                                    Amount
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-300 text-sm">
                                    Payment Type
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-300 text-sm">
                                    Payment Mode
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-300 text-sm">
                                    Creation
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-300 text-sm">
                                    Pushing
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 text-sm">
                                    Response Receive
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-300">
                            @foreach($months as $month)
                            <tr class="hover:bg-gray-50 transition-colors border-b border-gray-300">
                                <td class="px-4 py-3 text-gray-800 font-semibold align-middle border-r border-gray-300">
                                    {{ $month->name }}
                                </td>
                                
                                <td class="px-4 py-3 align-middle border-r border-gray-300 text-center">
                                    <input type="number" step="0.01" wire:model="settings.{{ strtolower($month->code) }}.amount" class="w-full max-w-[90px] mx-auto border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </td>
                                
                                <td class="px-4 py-3 align-middle border-r border-gray-300 text-center">
                                    <select wire:model="settings.{{ strtolower($month->code) }}.payment_type" class="w-full max-w-[120px] mx-auto border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">-- Type --</option>
                                        @foreach($paymentTypes as $type)
                                            <option value="{{ $type->code }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-4 py-3 align-middle border-r border-gray-300 text-center">
                                    <select wire:model="settings.{{ strtolower($month->code) }}.payment_mode" class="w-full max-w-[120px] mx-auto border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">-- Mode --</option>
                                        @foreach($paymentModes as $mode)
                                            <option value="{{ $mode->code }}">{{ $mode->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                
                                <!-- Creation (52301) -->
                                <td class="px-4 py-3 align-middle border-r border-gray-300">
                                    <div class="flex items-center justify-center space-x-3">
                                        <label class="inline-flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:model="settings.{{ strtolower($month->code) }}.52301.is_regular_lot" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                            <span class="ml-1 text-xs text-gray-700 font-medium whitespace-nowrap">Reg</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:model="settings.{{ strtolower($month->code) }}.52301.is_arrear_lot" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                            <span class="ml-1 text-xs text-gray-700 font-medium whitespace-nowrap">Arr</span>
                                        </label>
                                    </div>
                                </td>

                                <!-- Pushing (52302) -->
                                <td class="px-4 py-3 align-middle border-r border-gray-300">
                                    <div class="flex items-center justify-center space-x-3">
                                        <label class="inline-flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:model="settings.{{ strtolower($month->code) }}.52302.is_regular_lot" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                            <span class="ml-1 text-xs text-gray-700 font-medium whitespace-nowrap">Reg</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:model="settings.{{ strtolower($month->code) }}.52302.is_arrear_lot" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                            <span class="ml-1 text-xs text-gray-700 font-medium whitespace-nowrap">Arr</span>
                                        </label>
                                    </div>
                                </td>

                                <!-- Response Receive (52303) -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex items-center justify-center space-x-3">
                                        <label class="inline-flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:model="settings.{{ strtolower($month->code) }}.52303.is_regular_lot" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                            <span class="ml-1 text-xs text-gray-700 font-medium whitespace-nowrap">Reg</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:model="settings.{{ strtolower($month->code) }}.52303.is_arrear_lot" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                            <span class="ml-1 text-xs text-gray-700 font-medium whitespace-nowrap">Arr</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-8 flex justify-center">
                    <button wire:click="finalSubmit" style="background-color: #5c9ccc; color: white;" class="px-8 py-2.5 rounded shadow text-sm font-semibold tracking-wide hover:opacity-90 transition-opacity">
                        Final Submit
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endvolt
</x-app-layout>
