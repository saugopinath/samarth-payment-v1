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
                
          
            };



            with(function () {
                $allLotTypes = Codemaster::where('parent_short_code', 'lot_type')->where('is_active', true)->pluck('name', 'code')->toArray();
                $lotTypes = $allLotTypes;
                
                $allFinancialYears = FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                $financialYears = $allFinancialYears;

               

                $allMonths = Month::where('is_active', true)->orderBy('display_order')->pluck('name', 'code')->toArray();
                $months = $allMonths;


           
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

            $signAndPush = function ($lotNo) {
                try {
                    $service = app(\App\Services\PaymentLotXmlService::class);
                    $lotMaster = \App\Models\PaymentLotMaster::where('lot_no', $lotNo)->firstOrFail();
                    $result = $service->generateAndSignXml($lotMaster);
                    
                    $lotMaster->cur_status = '52103';
                    $lotMaster->save();
                    
                    session()->flash('status', 'Lot ' . $lotNo . ' successfully signed.');
                } catch (\Exception $e) {
                    session()->flash('status', 'Error signing lot ' . $lotNo . ': ' . $e->getMessage());
                }
            };

            $pushLot = function ($lotNo) {
                try {
                    $lotMaster = \App\Models\PaymentLotMaster::where('lot_no', $lotNo)->firstOrFail();
                    
                    // TODO: Implement actual SFTP/API push logic to SBI here
                     $service = app(\App\Services\PaymentLotXmlService::class);
                     $service->pushToSBI($lotMaster);

                    $lotMaster->cur_status = '52104';
                    $lotMaster->save();
                    
                    session()->flash('status', 'Lot ' . $lotNo . ' successfully pushed to SBI.');
                } catch (\Exception $e) {
                    session()->flash('status', 'Error pushing lot ' . $lotNo . ': ' . $e->getMessage());
                }
            };

            $defuncLot = function ($lotNo) {
                try {
                    $lotMaster = \App\Models\PaymentLotMaster::where('lot_no', $lotNo)->firstOrFail();
                    
                  

                    $lotMaster->cur_status = '52106';
                    $lotMaster->save();
                    
                    session()->flash('status', 'Lot ' . $lotNo . ' successfully marked as defunct.');
                } catch (\Exception $e) {
                    session()->flash('status', 'Error defuncting lot ' . $lotNo . ': ' . $e->getMessage());
                }
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
                        <select wire:model.live="scheme" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
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
                        <select wire:model.live="lot_financial_year" class="block w-full border-gray-200 rounded-md shadow-sm text-gray-600 focus:ring-orange-500 focus:border-orange-500 text-sm py-2">
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
                        <select wire:model.live="lot_month" class="block w-full border-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-2 text-gray-600">
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
                                    <th class="px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Lot Creation Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($lots as $lot)
                                    <tr class="hover:bg-orange-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $lot->lot_no }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $lot->created_at ? $lot->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <button class="text-orange-600 hover:text-orange-900 bg-orange-100 px-3 py-1 rounded-md text-xs font-bold transition-colors">View Details</button>
                                            @if($lot->cur_status == '52102')
                                                <button wire:click="signAndPush('{{ $lot->lot_no }}')" class="text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded-md text-xs font-bold transition-colors flex-inline items-center justify-center">
                                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                                                    Sign Lot
                                                </button>
                                            @endif
                                            @if($lot->cur_status == '52103')
                                                <button wire:click="pushLot('{{ $lot->lot_no }}')" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-md text-xs font-bold transition-colors flex-inline items-center justify-center">
                                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                    Push to SBI
                                                </button>
                                            @endif
                                            @if(in_array($lot->cur_status, ['52102', '52103']))
                                                <button wire:click="defuncLot('{{ $lot->lot_no }}')" class="text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded-md text-xs font-bold transition-colors flex-inline items-center justify-center">
                                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                                                    Defunc Lot
                                                </button>
                                            @endif
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
