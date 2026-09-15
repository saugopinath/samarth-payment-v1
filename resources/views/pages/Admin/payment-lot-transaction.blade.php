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
            use App\Models\FinancialYear;
            use App\Models\Month;
            use App\Models\PaymentLotMaster;

            state([
                'scheme_id' => '',
                'lot_financial_year' => '',
                'lot_month' => '',
            ]);

            with(function () {
                $schemes = Scheme::where('is_active', true)->get();
                $financialYears = FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                $months = Month::where('is_active', true)->orderBy('display_order')->pluck('name', 'code')->toArray();

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

                $lots = $query->orderBy('created_at', 'desc')->get();

                return compact('schemes', 'financialYears', 'months', 'lots');
            });

            $resetFilters = function () {
                $this->reset(['scheme_id', 'lot_financial_year', 'lot_month']);
            };

            $executeStep = function ($lotNo, $actionKey) {
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
                        <select wire:model.live="scheme_id" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">-- All Schemes --</option>
                            @foreach($schemes as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Financial Year</label>
                        <select wire:model.live="lot_financial_year" class="block w-full border-gray-200 rounded-md shadow-sm text-gray-600 focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
                            <option value="">-- All Years --</option>
                            @foreach($financialYears as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Month</label>
                        <select wire:model.live="lot_month" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">-- All Months --</option>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end mt-4">
                    <button wire:click="resetFilters" class="px-6 py-2 bg-gray-500 text-white rounded shadow-sm hover:bg-gray-600 font-semibold text-sm transition-colors">
                        Reset Filters
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
                                        {{ strtoupper($lot->payment_mode) }} <br>
                                        <span class="text-xs text-gray-400">Type: {{ $lot->integration_type ?? 'API' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ number_format($lot->ben_count) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                                        {{ number_format($lot->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $lot->cur_status ?? 'generated' }}
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
                                        No Payment Lots found matching the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
    @endvolt
</x-app-layout>
