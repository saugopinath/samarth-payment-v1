<?php
use function Laravel\Folio\{name, middleware};

name('admin.map-financial-year');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    @volt
    <div class="py-6 bg-[#f8f9fa] min-h-screen">
        <?php
            use function Livewire\Volt\{state, with};
            use App\Models\Scheme;
            use App\Models\Codemaster;
            use App\Models\Month;
            use App\Models\FinancialYear;
            state([
                'financial_year' => '',
                'show_results' => true,
                'show_modal' => false,
                'new_financial_year' => '',
                'new_is_active' => true,
                'show_amount_modal' => false,
                'selected_amount_scheme' => '',
                'lot_status' => [],
                'edit_lot' => [],
                'selected_schemes' => [],
                'monthly_amounts' => [
                    'January' => 1000, 'February' => 1000, 'March' => 1000, 'April' => 1000,
                    'May' => 1000, 'June' => 1000, 'July' => 1000, 'August' => 1000,
                    'September' => 1000, 'October' => 1000, 'November' => 1000, 'December' => 1000
                ]
            ]);

            with(function () {
                $years = FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                
                // Set default financial year to the latest one if not set
                if (empty($this->financial_year) && !empty($years)) {
                    $this->financial_year = array_key_last($years);
                }

                $mappedAmounts = \App\Models\SchemePaymentAmount::where('financial_year', $this->financial_year)->get();

                return [
                    'financialYears' => $years,
                    'schemes' => Scheme::where('is_active', true)->get(),
                    'dbMonths' => Month::where('is_active', true)->orderBy('display_order')->pluck('name')->toArray(),
                    'mappedAmounts' => $mappedAmounts,
                ];
            });

            $search = function () {
                if (empty($this->financial_year)) {
                    return;
                }

                $months = Month::where('is_active', true)->pluck('name')->toArray();
                $lots = \App\Models\FinancialYearMonthLot::where('financial_year', $this->financial_year)->get();
                
                foreach ($months as $month) {
                    $lot = $lots->firstWhere('month', $month);
                    $this->lot_status[$month] = [
                        'regular' => $lot ? $lot->is_regular_lot : false,
                        'arrear' => $lot ? $lot->is_arrear_lot : false,
                    ];
                }
                
                $this->show_results = true;
            };
            
            $toggleEditLot = function ($month) {
                if (!isset($this->edit_lot[$month])) {
                    $this->edit_lot[$month] = false;
                }
                
                if ($this->edit_lot[$month] && !empty($this->financial_year)) {
                    // Saving state to database
                    \App\Models\FinancialYearMonthLot::updateOrCreate(
                        ['financial_year' => $this->financial_year, 'month' => $month],
                        [
                            'is_regular_lot' => $this->lot_status[$month]['regular'] ?? false,
                            'is_arrear_lot' => $this->lot_status[$month]['arrear'] ?? false,
                        ]
                    );
                }

                $this->edit_lot[$month] = !$this->edit_lot[$month];
            };

            $mapScheme = function ($month) {
                $schemeId = $this->selected_schemes[$month] ?? null;
                if (!$schemeId) {
                    return;
                }

                if (empty($this->financial_year)) {
                    return;
                }

                $monthField = strtolower($month) . '_amount';

                $record = \App\Models\SchemePaymentAmount::firstOrCreate(
                    [
                        'scheme_id' => $schemeId,
                        'financial_year' => $this->financial_year,
                    ],
                    [
                        'january_amount' => 0, 'february_amount' => 0, 'march_amount' => 0, 'april_amount' => 0,
                        'may_amount' => 0, 'june_amount' => 0, 'july_amount' => 0, 'august_amount' => 0,
                        'september_amount' => 0, 'october_amount' => 0, 'november_amount' => 0, 'december_amount' => 0,
                    ]
                );

                if ($record->$monthField == 0) {
                    $record->$monthField = 1000;
                    $record->save();
                }

                $this->selected_schemes[$month] = '';
            };

            $unmapScheme = function ($month, $schemeId) {
                if (empty($this->financial_year)) {
                    return;
                }

                $monthField = strtolower($month) . '_amount';
                $record = \App\Models\SchemePaymentAmount::where('scheme_id', $schemeId)
                    ->where('financial_year', $this->financial_year)
                    ->first();

                if ($record) {
                    $record->$monthField = 0;
                    $record->save();
                    
                    // If all months are 0, we could delete the record, but leaving it is fine.
                }
            };

            $openModal = function () {
                $this->new_financial_year = '';
                $this->new_is_active = true;
                $this->show_modal = true;
            };

            $closeModal = function () {
                $this->show_modal = false;
            };
            
            $openAmountModal = function () {
                $this->show_amount_modal = true;
            };
            
            $closeAmountModal = function () {
                $this->show_amount_modal = false;
            };
            
            $saveAmounts = function () {
                $this->validate([
                    'selected_amount_scheme' => 'required',
                    'monthly_amounts.*' => 'required|numeric|min:0'
                ], [
                    'selected_amount_scheme.required' => 'Please select a scheme.',
                    'monthly_amounts.*.required' => 'All monthly amounts are required.',
                    'monthly_amounts.*.numeric' => 'Amounts must be valid numbers.',
                ]);

                \App\Models\SchemePaymentAmount::updateOrCreate(
                    [
                        'scheme_id' => $this->selected_amount_scheme,
                        'financial_year' => $this->financial_year, // Currently selected financial year on the page
                    ],
                    [
                        'january_amount' => $this->monthly_amounts['January'],
                        'february_amount' => $this->monthly_amounts['February'],
                        'march_amount' => $this->monthly_amounts['March'],
                        'april_amount' => $this->monthly_amounts['April'],
                        'may_amount' => $this->monthly_amounts['May'],
                        'june_amount' => $this->monthly_amounts['June'],
                        'july_amount' => $this->monthly_amounts['July'],
                        'august_amount' => $this->monthly_amounts['August'],
                        'september_amount' => $this->monthly_amounts['September'],
                        'october_amount' => $this->monthly_amounts['October'],
                        'november_amount' => $this->monthly_amounts['November'],
                        'december_amount' => $this->monthly_amounts['December'],
                    ]
                );

                session()->flash('status', 'Amounts saved successfully.');
                $this->show_amount_modal = false;
            };

            $saveFinancialYear = function () {
                $this->validate([
                    'new_financial_year' => 'required|string|regex:/^\d{4}-\d{4}$/',
                ], [
                    'new_financial_year.regex' => 'The financial year must be in the format YYYY-YYYY (e.g. 2025-2026).',
                ]);

                // Check if it already exists
                $exists = FinancialYear::where('name', $this->new_financial_year)->exists();
                if ($exists) {
                    $this->addError('new_financial_year', 'This financial year already exists.');
                    return;
                }

                $maxCode = FinancialYear::max('code') ?? 6000;
                $newCode = $maxCode + 1;

                FinancialYear::create([
                    'name' => $this->new_financial_year,
                    'code' => $newCode,
                    'is_active' => $this->new_is_active,
                ]);
                
                $this->financial_year = $newCode;
                session()->flash('status', 'Financial year added and selected successfully!');
                $this->show_modal = false;
            };
        ?>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <!-- Page Header -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-t-lg overflow-hidden">
                <div class="px-4 py-4 border-b border-gray-200 flex items-center text-blue-600">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                    <h2 class="text-lg font-semibold text-gray-700">Map Financial Year for Payment</h2>
                </div>
            </div>

            <!-- Top Panel: Scheme & Financial Year -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden p-5 relative">
                <!-- Pill Badge -->
                <div class="inline-block bg-[#0066cc] text-white text-sm font-semibold px-4 py-1.5 rounded-full mb-6">
                    Scheme & Financial Year
                </div>
                
                <div class="max-w-xs mb-6">
                    <label class="block text-sm font-bold text-gray-800 mb-1">Financial Year <span class="text-red-500">*</span></label>
                    <select wire:model="financial_year" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-2 text-gray-700">
                        @foreach($financialYears as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                    <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <button wire:click="search" class="px-4 py-1.5 bg-[#17a2b8] text-white rounded shadow-sm hover:bg-[#138496] font-semibold text-sm flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Search
                    </button>
                    
                    <button wire:click="openModal" class="px-4 py-1.5 bg-[#ffc107] text-gray-900 rounded shadow-sm hover:bg-[#e0a800] font-semibold text-sm flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path><path d="M10 9a1 1 0 00-1 1v1H8a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1v-1a1 1 0 00-1-1z"></path></svg>
                        Add Financial Year
                    </button>
                    
                    <button wire:click="openAmountModal" class="px-4 py-1.5 bg-blue-600 text-white rounded shadow-sm hover:bg-blue-700 font-semibold text-sm flex items-center transition-colors">
                        <span class="mr-1.5 font-bold">₹</span>
                        Define Amount
                    </button>
                </div>
            </div>

            <!-- Bottom Panel: Add & Map Financial Year -->
            @if($show_results)
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden p-5">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <!-- Pill Badge -->
                        <div class="inline-block bg-[#0066cc] text-white text-sm font-semibold px-4 py-1.5 rounded-full mb-3">
                            Add & Map Financial Year
                        </div>
                        <h3 class="text-sm font-bold text-green-700">Financial Year : 2026-2027</h3>
                    </div>
                    
                    <button class="px-4 py-1.5 bg-[#28a745] text-white rounded shadow-sm hover:bg-[#218838] font-semibold text-sm flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        Add New
                    </button>
                </div>
                
                <div class="mt-4 overflow-x-auto border border-gray-200 rounded">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 border-r border-gray-200 uppercase text-xs w-24">
                                    Month
                                </th>
                                <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 border-r border-gray-200 uppercase text-xs">
                                    Already Mapped Scheme
                                </th>
                                <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 border-r border-gray-200 uppercase text-xs w-48">
                                    Map Scheme
                                </th>
                                <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 uppercase text-xs w-32">
                                    Lot Type
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($dbMonths as $mIndex => $month)
                            <tr>
                                <td class="px-4 py-3 text-gray-600 align-top border-r border-gray-200 w-24">
                                    {{ $month }}
                                </td>
                                <td class="px-4 py-3 align-top border-r border-gray-200">
                                    <div class="space-y-3">
                                        @php
                                            $monthField = strtolower($month) . '_amount';
                                            $count = 1;
                                        @endphp
                                        @foreach($mappedAmounts as $mapped)
                                            @if(isset($mapped->$monthField) && $mapped->$monthField > 0)
                                                @php $sch = $schemes->firstWhere('id', $mapped->scheme_id); @endphp
                                                @if($sch)
                                                    <div class="flex items-start justify-between">
                                                        <div>
                                                            <span class="font-bold text-gray-800">{{ $count++ }}.</span> <span class="text-gray-700">{{ $sch->display_name ?? $sch->name }}</span><br>
                                                            <span class="text-[#0066cc] font-bold text-xs italic">Disbursed Amount : {{ $mapped->$monthField }}</span>
                                                        </div>
                                                        <button wire:click="unmapScheme('{{ $month }}', {{ $mapped->scheme_id }})" class="text-gray-700 hover:text-red-600 transition-colors border-2 border-black rounded px-1 py-0.5 ml-2">
                                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                                        </button>
                                                    </div>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top border-r border-gray-200 w-64">
                                    <div class="flex items-center space-x-2">
                                        <select wire:model="selected_schemes.{{ $month }}" class="border-gray-400 rounded shadow-sm text-sm py-1.5 focus:ring-blue-500 focus:border-blue-500 flex-1">
                                            <option value="">Select Scheme</option>
                                            @foreach($schemes as $scheme)
                                                <option value="{{ $scheme->id }}">{{ $scheme->name }}</option>
                                            @endforeach
                                        </select>
                                        <button wire:click="mapScheme('{{ $month }}')" class="bg-[#28a745] text-white px-3 py-1.5 rounded shadow text-sm font-semibold hover:bg-[#218838] flex items-center transition-colors">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                            Map
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top w-32">
                                    @if(isset($edit_lot[$month]) && $edit_lot[$month])
                                        <div class="space-y-2">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model="lot_status.{{ $month }}.regular" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700 font-medium">Regular Lot</span>
                                            </label>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model="lot_status.{{ $month }}.arrear" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700 font-medium">Arrear Lot</span>
                                            </label>
                                            <button wire:click="toggleEditLot('{{ $month }}')" class="text-xs text-blue-600 underline font-bold">Done</button>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <span class="text-[#008080] font-bold text-sm text-center">
                                                {{ (isset($lot_status[$month]['regular']) && $lot_status[$month]['regular']) ? 'Regular Lot' : '' }}
                                                {{ (isset($lot_status[$month]['regular']) && $lot_status[$month]['regular'] && isset($lot_status[$month]['arrear']) && $lot_status[$month]['arrear']) ? ' & ' : '' }}
                                                {{ (isset($lot_status[$month]['arrear']) && $lot_status[$month]['arrear']) ? 'Arrear Lot' : '' }}
                                                {{ (!isset($lot_status[$month]['regular']) || !$lot_status[$month]['regular']) && (!isset($lot_status[$month]['arrear']) || !$lot_status[$month]['arrear']) ? 'None' : '' }}
                                            </span>
                                            <button wire:click="toggleEditLot('{{ $month }}')" class="mt-1 border-2 border-black rounded px-1 py-0.5 hover:bg-gray-100 transition-colors">
                                                <svg class="w-3.5 h-3.5 text-black" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                </table>
                </div>
            </div>
            @endif

        </div>

        <!-- Add Financial Year Modal -->
        @if($show_modal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Modal Panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-semibold text-gray-900" id="modal-title">
                            Add Financial Year
                        </h3>
                    </div>
                    <div class="bg-white px-6 pt-5 pb-6 space-y-5">
                        <div>
                            <label for="new_financial_year" class="block text-sm font-medium text-gray-700 mb-1">Financial Year <span class="text-red-500">*</span></label>
                            <input type="text" id="new_financial_year" wire:model="new_financial_year" placeholder="e.g. 2025-2026" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-2 text-gray-700">
                            @error('new_financial_year') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            <p class="text-gray-400 text-xs mt-1">Format: YYYY-YYYY</p>
                        </div>
                        <div>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="new_is_active" class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 font-medium">Is Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="button" wire:click="saveFinancialYear" class="w-full inline-flex justify-center rounded border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Save
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Define Amount Modal -->
        @if($show_amount_modal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="amount-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Modal Panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-semibold text-gray-900" id="amount-modal-title">
                            Define Payable Amount per Month
                        </h3>
                    </div>
                    <div class="bg-white px-6 pt-5 pb-6">
                        <!-- Scheme Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Scheme <span class="text-red-500">*</span></label>
                            <select wire:model="selected_amount_scheme" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-2 text-gray-700">
                                <option value="">---Select Scheme---</option>
                                @foreach($schemes as $sch)
                                    <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $month }} <span class="text-red-500">*</span></label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₹</span>
                                    </div>
                                    <input type="number" wire:model="monthly_amounts.{{ $month }}" class="block w-full pl-7 pr-3 py-2 border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 text-sm text-gray-700" placeholder="0.00">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="button" wire:click="saveAmounts" class="w-full inline-flex justify-center rounded border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Save Amounts
                        </button>
                        <button type="button" wire:click="closeAmountModal" class="mt-3 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endvolt
</x-app-layout>
