<?php
use function Laravel\Folio\{name, middleware};

name('admin.financial-year-months');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-orange-500 leading-tight">
                {{ __('Financial Year Months') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">View all months for a specific financial year.</p>
        </div>
    </x-slot>

    @volt
    <div class="py-8 bg-[#fdfaf5] min-h-screen">
        <?php
            use function Livewire\Volt\{state, with};
            use App\Models\FinancialYear;
            use App\Models\Month;

            state([
                'selected_scheme' => '',
                'selected_financial_year' => '',
                'show_months' => false,
                'show_modal' => false,
                'new_financial_year' => '',
                'new_is_active' => true,
            ]);

            with(function () {
                $schemes = \App\Models\Scheme::where('is_active', true)->get();
                $financialYears = \App\Models\FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                
                $months = collect();
                $monthLots = collect();
                
                if ($this->show_months) {
                    $months = \App\Models\Month::where('is_active', true)->orderBy('display_order')->get();
                    
                    // Fetch existing settings for this scheme and year
                    $monthLots = \App\Models\FinancialYearMonthPaymentLot::where('scheme_id', $this->selected_scheme)
                        ->where('financial_year', $this->selected_financial_year)
                        ->where('is_active', true)
                        ->get()
                        ->groupBy('month');
                }

                return [
                    'schemes' => $schemes,
                    'financialYears' => $financialYears,
                    'months' => $months,
                    'monthLots' => $monthLots,
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
                
                $this->show_months = true;
            };

            $updatedSelectedScheme = function () {
                $this->show_months = false;
            };

            $updatedSelectedFinancialYear = function () {
                $this->show_months = false;
            };

            $toggleOption = function ($monthCode, $lotType, $optionType) {
                if (empty($this->selected_scheme) || empty($this->selected_financial_year)) {
                    return;
                }

                $settingType = "{$lotType}_{$optionType}";

                $lot = \App\Models\FinancialYearMonthLot::firstOrNew([
                    'scheme_id' => $this->selected_scheme,
                    'financial_year' => $this->selected_financial_year,
                    'month' => $monthCode,
                    'type' => $settingType,
                ]);

                $lot->is_active = !$lot->is_active;
                $lot->save();
            };

            $openModal = function () {
                $this->new_financial_year = '';
                $this->new_is_active = true;
                $this->show_modal = true;
            };

            $closeModal = function () {
                $this->show_modal = false;
            };

            $saveFinancialYear = function () {
                $this->validate([
                    'new_financial_year' => 'required|string|regex:/^\d{4}-\d{4}$/',
                ], [
                    'new_financial_year.regex' => 'The financial year must be in the format YYYY-YYYY (e.g. 2025-2026).',
                ]);

                // Check if it already exists
                $exists = \App\Models\FinancialYear::where('name', $this->new_financial_year)->exists();
                if ($exists) {
                    $this->addError('new_financial_year', 'This financial year already exists.');
                    return;
                }

                $maxCode = \App\Models\FinancialYear::max('code') ?? 6000;
                $newCode = $maxCode + 1;

                \App\Models\FinancialYear::create([
                    'name' => $this->new_financial_year,
                    'code' => $newCode,
                    'is_active' => $this->new_is_active,
                ]);
                
                $this->selected_financial_year = $newCode;
                $this->show_months = false;
                session()->flash('status', 'Financial year added and selected successfully!');
                $this->show_modal = false;
            };
        ?>
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <!-- Selection Card -->
            <div class="relative bg-white shadow-sm border border-orange-200 rounded-lg p-6 pt-10">
                <div class="absolute -top-4 left-6 flex space-x-2">
                    <span class="bg-orange-400 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Selection Criteria
                    </span>
                </div>
                <div class="absolute -top-4 right-6">
                    <button wire:click="openModal" class="bg-white border border-orange-300 text-orange-600 hover:bg-orange-50 px-4 py-1.5 rounded-lg text-sm font-bold shadow-sm tracking-wide flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Financial Year
                    </button>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 mt-2 ml-2 items-end">
                    <div class="sm:col-span-5">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Scheme <span class="text-red-500">*</span></label>
                        <select wire:model.live="selected_scheme" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2.5 text-gray-600 transition-colors">
                            <option value="">---Select Scheme---</option>
                            @foreach($schemes as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                            @endforeach
                        </select>
                        @error('selected_scheme') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-5">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Financial Year <span class="text-red-500">*</span></label>
                        <select wire:model.live="selected_financial_year" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2.5 text-gray-600 transition-colors">
                            <option value="">---Select Financial Year---</option>
                            @foreach($financialYears as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('selected_financial_year') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <button wire:click="showMonthsList" class="w-full px-6 py-2.5 bg-orange-500 text-white rounded-md shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 font-bold text-sm flex items-center justify-center transition-all duration-200 transform hover:scale-[1.02]">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            OK
                        </button>
                    </div>
                </div>
            </div>

            <!-- Months List Card -->
            @if($show_months)
            <div class="relative bg-white shadow-sm border border-blue-200 rounded-lg p-6 pt-10 animate-[fadeIn_0.3s_ease-in-out]">
                <span class="absolute -top-4 left-6 bg-blue-500 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Month Configurations
                </span>

                <div class="mt-4 overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 border-r border-gray-200 uppercase text-xs w-32">
                                    Month
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-200 uppercase text-xs">
                                    Regular Lot Options
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 uppercase text-xs">
                                    Arrear Lot Options
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($months as $month)
                            @php
                                $lots = $monthLots[$month->code] ?? collect();
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-800 font-semibold align-middle border-r border-gray-200 w-32">
                                    {{ $month->name }}
                                </td>
                                <td class="px-4 py-3 align-middle border-r border-gray-200">
                                    <div class="flex items-center justify-center space-x-6">
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:click="toggleOption('{{ $month->code }}', 'regular', 'create')" {{ $lots->where('type', 'regular_create')->first() ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                            <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-blue-600">Create</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:click="toggleOption('{{ $month->code }}', 'regular', 'push')" {{ $lots->where('type', 'regular_push')->first() ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 cursor-pointer">
                                            <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-purple-600">Push</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:click="toggleOption('{{ $month->code }}', 'regular', 'receive')" {{ $lots->where('type', 'regular_receive')->first() ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-pointer">
                                            <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-green-600">Receive</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex items-center justify-center space-x-6">
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:click="toggleOption('{{ $month->code }}', 'arrear', 'create')" {{ $lots->where('type', 'arrear_create')->first() ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                            <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-blue-600">Create</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:click="toggleOption('{{ $month->code }}', 'arrear', 'push')" {{ $lots->where('type', 'arrear_push')->first() ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 cursor-pointer">
                                            <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-purple-600">Push</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="checkbox" wire:click="toggleOption('{{ $month->code }}', 'arrear', 'receive')" {{ $lots->where('type', 'arrear_receive')->first() ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-pointer">
                                            <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-green-600">Receive</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500 font-medium">
                                    No months found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Add Financial Year Modal -->
            @if($show_modal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <!-- Modal Panel -->
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-[fadeIn_0.2s_ease-in-out]">
                        <div class="bg-orange-50 px-6 py-4 border-b border-orange-100 flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-bold text-orange-800" id="modal-title">
                                Add Financial Year
                            </h3>
                            <button wire:click="closeModal" class="text-orange-400 hover:text-orange-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 space-y-5">
                            <div>
                                <label for="new_financial_year" class="block text-sm font-semibold text-gray-700 mb-1">Financial Year <span class="text-red-500">*</span></label>
                                <input type="text" id="new_financial_year" wire:model="new_financial_year" placeholder="e.g. 2025-2026" class="block w-full border-gray-300 rounded shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2.5 text-gray-700">
                                @error('new_financial_year') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                                <p class="text-gray-400 text-xs mt-1 font-medium">Format: YYYY-YYYY</p>
                            </div>
                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="new_is_active" class="form-checkbox h-5 w-5 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                                    <span class="ml-2 text-sm text-gray-700 font-semibold">Is Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                            <button type="button" wire:click="saveFinancialYear" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-500 text-base font-bold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                Save
                            </button>
                            <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                Cancel
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
