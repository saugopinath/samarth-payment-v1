<?php
use function Laravel\Folio\{name, middleware};

name('admin.payment-lot-generation');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-orange-500 leading-tight">
                {{ __('Payment Lot Generation') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Manage scheme criteria, lot type, and pending beneficiary details.</p>
        </div>
    </x-slot>

    @volt
    <div class="py-8 bg-[#fdfaf5] min-h-screen">
        <?php
            use function Livewire\Volt\{state, with};
            use App\Models\Scheme;
            use App\Models\Codemaster;

            state([
                'payment_type' => 6001,
                'scheme_criteria' => 'lot_wise_beneficiary',
                'scheme' => '',
                'lot_type' => 6051,
                'lot_financial_year' => 6032,
                'lot_month' => '',
                'target_payment_mode' => '',
            ]);

            with(fn () => [
                'schemes' => Scheme::where('is_active', true)->get(),
                'paymentTypes' => Codemaster::where('parent_short_code', 'payment_type')->where('is_active', true)->pluck('name', 'code')->toArray(),
                'months' => Codemaster::where('parent_short_code', 'lot_month')->where('is_active', true)->pluck('name', 'code')->toArray(),
                'financialYears' => Codemaster::where('parent_short_code', 'financial_year')->where('is_active', true)->pluck('name', 'code')->toArray(),
                'targetPaymentModes' => Codemaster::where('parent_short_code', 'target_payment_mode')->where('is_active', true)->pluck('name', 'code')->toArray(),
                'lotTypes' => Codemaster::where('parent_short_code', 'lot_type')->where('is_active', true)->pluck('name', 'code')->toArray()
            ]);

            $resetForm = function () {
                $this->reset([
                    'payment_type',
                    'scheme_criteria',
                    'scheme',
                    'lot_type',
                    'lot_financial_year',
                    'lot_month',
                    'target_payment_mode'
                ]);
                
                // Set default values back
                $this->payment_type = 6001;
                $this->scheme_criteria = 'lot_wise_beneficiary';
                $this->lot_type = 6051;
                $this->lot_financial_year = 6032;
            };

            $preview = function () {
                // Logic for preview
            };

            $createLot = function () {
                // Logic for creating lot
            };
        ?>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Payment Mode Selection -->
            <div class="relative bg-white shadow-sm border border-orange-200 rounded-lg p-6 pt-10">
                <span class="absolute -top-4 left-6 bg-orange-500 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide">
                    Payment Mode Selection
                </span>
                <div class="flex justify-center mt-2">
                    <div class="w-full max-w-lg bg-gray-50/50 p-4 rounded-lg border border-gray-100">
                        <label class="block text-sm font-semibold text-gray-800 text-center mb-3">Select Payment Type <span class="text-red-500">*</span></label>
                        <select wire:model="payment_type" class="block w-full border-gray-200 bg-gray-100 text-gray-700 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-center text-sm py-2">
                            @foreach($paymentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Scheme Criteria -->
            <div class="relative bg-white shadow-sm border border-orange-200 rounded-lg p-6 pt-10">
                <span class="absolute -top-4 left-6 bg-orange-400 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Scheme Criteria
                </span>
                
                <div class="mt-2 space-y-6">
                    <div class="flex items-center ml-2">
                        <input wire:model="scheme_criteria" id="lot_wise_beneficiary" type="radio" value="lot_wise_beneficiary" class="w-4 h-4 text-orange-500 bg-gray-100 border-gray-300 focus:ring-orange-500 cursor-pointer">
                        <label for="lot_wise_beneficiary" class="ml-2 text-sm font-semibold text-gray-800 cursor-pointer">Lot Wise Beneficiary</label>
                    </div>

                    <div class="w-full max-w-md ml-2">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Select Scheme <span class="text-red-500">*</span></label>
                        <select wire:model="scheme" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">---Select Scheme---</option>
                            @foreach($schemes as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Lot Type, Month & Year -->
            <div class="relative bg-white shadow-sm border border-orange-200 rounded-lg p-6 pt-10">
                <span class="absolute -top-4 left-6 bg-orange-400 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Lot Type, Month & Year
                </span>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-2 ml-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Lot Type <span class="text-red-500">*</span></label>
                        <select wire:model="lot_type" class="block w-full border-gray-200 rounded-md shadow-sm text-gray-600 focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
                            @foreach($lotTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Lot Financial Year <span class="text-red-500">*</span></label>
                        <select wire:model="lot_financial_year" class="block w-full border-gray-200 rounded-md shadow-sm text-gray-600 focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
                            @foreach($financialYears as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Lot Month <span class="text-red-500">*</span></label>
                        <select wire:model="lot_month" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                            <option value="">Select Month</option>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pending Beneficiary & Amount -->
            <div class="relative bg-white shadow-sm border border-orange-200 rounded-lg p-6 pt-10">
                <span class="absolute -top-4 left-6 bg-orange-400 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Pending Beneficiary & Amount
                </span>
                
                <div class="mt-2 w-full max-w-md ml-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Target Payment Mode <span class="text-red-500">*</span></label>
                    <select wire:model="target_payment_mode" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
                        <option value="">---Select Target Payment Mode---</option>
                        @foreach($targetPaymentModes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-center items-center space-x-4 pt-4 pb-8 border-t border-gray-200 mt-8">
                <button wire:click="resetForm" class="px-8 py-2.5 bg-gray-500 text-white rounded shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 font-semibold text-sm transition-colors">
                    Reset
                </button>
                <button wire:click="preview" class="px-8 py-2.5 bg-yellow-400 text-gray-900 rounded shadow-sm hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 font-semibold text-sm flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Preview
                </button>
                <button wire:click="createLot" class="px-8 py-2.5 bg-orange-300 text-orange-900 rounded shadow-sm hover:bg-orange-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-300 font-semibold text-sm flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Create Lot
                </button>
            </div>
            
        </div>
    </div>
    @endvolt
</x-app-layout>
