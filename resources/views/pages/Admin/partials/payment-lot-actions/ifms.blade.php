<button class="text-orange-600 hover:text-orange-900 bg-orange-100 px-3 py-1 rounded-md text-xs font-bold transition-colors">View Details</button>
@if($lot->cur_status == config('payment_lot.status.common.generated'))
    <button wire:click="pushLot('{{ $lot->lot_no }}')" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-md text-xs font-bold transition-colors inline-flex items-center justify-center">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
        {{ $integrationType == 'api' ? 'Push to IFMS API' : 'Push to IFMS' }}
    </button>
@endif
@if($lot->cur_status == config('payment_lot.status.common.push'))
    <button wire:click="checkdotDone('{{ $lot->lot_no }}')" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-md text-xs font-bold transition-colors inline-flex items-center justify-center">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
        {{ $integrationType == 'api' ? 'Check API Acknowledge' : 'IFMS received?' }}
    </button>
@endif
@if($lot->cur_status == config('payment_lot.status.ifms.dotdone'))
    <button wire:click="checkAcknowledge('{{ $lot->lot_no }}')" class="text-purple-600 hover:text-purple-900 bg-purple-100 px-3 py-1 rounded-md text-xs font-bold transition-colors inline-flex items-center justify-center">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
        {{ $integrationType == 'api' ? 'Check API Response' : 'Submitted To Treasury' }}
    </button>
@endif
@if($lot->cur_status == config('payment_lot.status.common.ack'))
    <button wire:click="checkResponse('{{ $lot->lot_no }}')" class="text-purple-600 hover:text-purple-900 bg-purple-100 px-3 py-1 rounded-md text-xs font-bold transition-colors inline-flex items-center justify-center">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
        {{ $integrationType == 'api' ? 'Check API Response' : 'Import RBI Report' }}
    </button>
@endif


@if(in_array($lot->cur_status, [config('payment_lot.status.common.generated')]))
    <button wire:click="defuncLot('{{ $lot->lot_no }}')" class="text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded-md text-xs font-bold transition-colors inline-flex items-center justify-center">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
        Defunc Lot
    </button>
@endif
