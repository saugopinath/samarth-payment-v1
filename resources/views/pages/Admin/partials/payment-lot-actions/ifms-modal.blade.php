
@once
<template x-teleport="body">
    <div x-data="{ open: false, lotNo: '', schemeId: '', treasuryCode: '', ddoCode: '', hoaId: '', amount: '' }"
         @open-bill-modal.window="
            open = true; 
            lotNo = $event.detail.lot_no; 
            schemeId = $event.detail.scheme_id;
            treasuryCode = $event.detail.treasury_code;
            ddoCode = $event.detail.ddo_code;
            hoaId = $event.detail.hoa_id;
            amount = $event.detail.amount;
         "
         x-show="open"
         style="display: none;"
         class="fixed inset-0 z-[100] overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 @click="open = false" aria-hidden="true"></div>

            <!-- Center modal trick -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                
                <!-- Header -->
                <div class="bg-blue-600 px-4 py-3 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-white" id="modal-title">
                        Bill Generation
                    </h3>
                    <button type="button" @click="open = false" class="text-white hover:text-gray-200 focus:outline-none">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="px-4 pt-5 pb-4 sm:p-6 bg-gray-50">
                    <p id="header_message" class="text-center text-green-600 font-bold mb-4"></p>

                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 rounded-t-lg">
                            <h2 class="text-md font-bold text-blue-600 flex items-center gap-2">
                                Bill Details
                                <span class="text-sm text-cyan-600 font-bold">
                                    (Lot No: <span x-text="lotNo"></span>)
                                </span>
                            </h2>
                        </div>

                        <div class="p-4 space-y-6">
                            <!-- Codes -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Treasury Code</label>
                                    <div class="mt-1 text-lg font-bold text-gray-900" x-text="treasuryCode"></div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700">DDO Code</label>
                                    <div class="mt-1 text-lg font-bold text-gray-900" x-text="ddoCode"></div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Head of Account</label>
                                    <div class="mt-1 text-lg font-bold text-gray-900" x-text="hoaId"></div>
                                </div>
                            </div>

                            <hr class="border-gray-200">

                            <!-- Amounts -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Gross Amount (₹)</label>
                                    <div class="mt-1 text-lg font-bold text-blue-600" x-text="amount"></div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Net Amount (₹)</label>
                                    <div class="mt-1 text-lg font-bold text-green-600" x-text="amount"></div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Sanction Amount (₹)</label>
                                    <div class="mt-1 text-lg font-bold text-cyan-600" x-text="amount"></div>
                                </div>
                            </div>

                            <hr class="border-gray-200">

                            <!-- Sanction Info -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sanction Number <span class="text-red-500">*</span></label>
                                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" id="senctionNumber">
                                    <small id="error_senctionNumber" class="text-red-500 text-xs mt-1"></small>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sanction Date <span class="text-red-500">*</span></label>
                                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" id="senctionDate" placeholder="DD/MM/YYYY">
                                    <small id="error_senctionDate" class="text-red-500 text-xs mt-1"></small>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Issuing Authority <span class="text-red-500">*</span></label>
                                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" id="issueingAuth">
                                    <small id="error_issueingAuth" class="text-red-500 text-xs mt-1"></small>
                                </div>
                            </div>
                            
                            <!-- Bill Info -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Bill Number <span class="text-red-500">*</span></label>
                                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" id="bill_no">
                                    <small id="error_billNumber" class="text-red-500 text-xs mt-1"></small>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Bill Date <span class="text-red-500">*</span></label>
                                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" id="bill_date" value="{{ date('d/m/Y') }}" placeholder="DD/MM/YYYY">
                                    <small id="error_billDate" class="text-red-500 text-xs mt-1"></small>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-center space-x-3">

                        <button type="button" @click="$wire.billshare(lotNo); open = false" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-md text-xs font-bold transition-colors inline-flex items-center justify-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            Bill Share
                        </button>
                    

                        <button type="button" @click="open = false" id="btnSubmitCancel" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Reset / Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
@endonce
